<?php

require __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/db.php';

function rb_slugify(string $str): string {
    $str = strtolower($str);
    
    $str = preg_replace('~[^a-z0-9]+~', '-', $str);
    $str = trim($str, '-');
    return $str ?: '';
}

$activePage = 'products';


$errors = [];


$baseCategories = ['Consoles', 'Handhelds', 'Games', 'Accessories'];

try {
    $catStmt = $pdo->query("
        SELECT DISTINCT category
        FROM products
        WHERE category IS NOT NULL AND category <> ''
    ");
    $fromDb = $catStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $fromDb = [];
}

$fromDb = array_filter($fromDb);

$normalizedFromDb = [];
foreach ($fromDb as $cat) {
    $cat = trim($cat);
    if ($cat === '') continue;

    switch (strtolower($cat)) {
        case 'console':
            $cat = 'Consoles';
            break;
        case 'handheld':
            $cat = 'Handhelds';
            break;
    }

    $normalizedFromDb[] = $cat;
}

$extra = array_diff($normalizedFromDb, $baseCategories);

$categories = array_values(array_unique(array_merge($baseCategories, $extra)));

$title       = $_POST['title']       ?? '';
$price       = $_POST['price']       ?? '';
$year_made   = $_POST['year_made']   ?? '';
$tag         = $_POST['tag']         ?? '';
$short_desc  = $_POST['short_desc']  ?? '';
$description = $_POST['description'] ?? '';

$categorySelect = $_POST['category_select'] ?? '';
$categoryCustom = $_POST['category_custom'] ?? '';
$category       = '';

$discount_price = $_POST['discount_price'] ?? '';
$has_discount   = isset($_POST['has_discount']);

$spec_keys   = $_POST['spec_key']   ?? [];
$spec_values = $_POST['spec_value'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title       = trim($title);
    $price       = trim($price);
    $year_made   = trim($year_made);
    $tag         = trim($tag);
    $short_desc  = trim($short_desc);
    $description = trim($description);
    $categorySelect = trim($categorySelect);
    $categoryCustom = trim($categoryCustom);

    if ($categorySelect === '_custom_') {
        $category = $categoryCustom;
    } else {
        $category = $categorySelect;
    }

    $specs = [];
    if (is_array($spec_keys) && is_array($spec_values)) {
        foreach ($spec_keys as $i => $k) {
            $k = trim($k ?? '');
            $v = trim($spec_values[$i] ?? '');
            if ($k === '' && $v === '') {
                continue;
            }
            if ($k === '' || $v === '') {
                
                continue;
            }
            $specs[] = [
                'key'   => $k,
                'value' => $v,
            ];
        }
    }

    
    if ($title === '') {
        $errors[] = 'Title is required.';
    }
    if ($price === '' || !is_numeric($price)) {
        $errors[] = 'A valid price is required.';
    }
    if ($year_made !== '' && !ctype_digit($year_made)) {
        $errors[] = 'Year must be a number (e.g. 1995).';
    }
    if ($has_discount) {
        if ($discount_price === '' || !is_numeric($discount_price)) {
            $errors[] = 'Discount price must be a valid number.';
        } elseif ($price !== '' && is_numeric($price) && (float)$discount_price >= (float)$price) {
            $errors[] = 'Discount price must be less than regular price.';
        }
    }

    $discountValue = null;
    if ($has_discount && $discount_price !== '' && is_numeric($discount_price)) {
        $discountValue = (float)$discount_price;
    }

    if (empty($errors)) {
        
        $stmt = $pdo->prepare("
            INSERT INTO products 
                (title, category, short_desc, description, price, discount_price, year_made, tag)
            VALUES 
                (:title, :category, :short_desc, :description, :price, :discount_price, :year_made, :tag)
        ");

        $stmt->execute([
            ':title'          => $title,
            ':category'       => $category !== '' ? $category : null,
            ':short_desc'     => $short_desc !== '' ? $short_desc : null,
            ':description'    => $description !== '' ? $description : null,
            ':price'          => (float)$price,
            ':discount_price' => $discountValue,
            ':year_made'      => $year_made !== '' ? (int)$year_made : null,
            ':tag'            => $tag !== '' ? $tag : null,
        ]);

        $productId = (int)$pdo->lastInsertId();

                
        $uploadedPaths = [];

        if (!empty($_FILES['images']) && !empty($_FILES['images']['name'])) {

            
            $baseFs  = __DIR__ . '/../assets/images/products/';
            $baseUrl = 'assets/images/products/';

           
            $slug = rb_slugify($title);
            if ($slug === '') {
                $slug = 'product-' . $productId;
            }

            $uploadDirFs  = $baseFs . $slug . '/';
            $uploadDirUrl = $baseUrl . $slug . '/';

            $allowedTypes = [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp'
            ];

            if (!is_dir($uploadDirFs)) {
                mkdir($uploadDirFs, 0775, true);
            }

            $names    = $_FILES['images']['name'];
            $tmp      = $_FILES['images']['tmp_name'];
            $errorsUp = $_FILES['images']['error'];
            $types    = $_FILES['images']['type'];

            foreach ($names as $idx => $origName) {
                if ($errorsUp[$idx] !== UPLOAD_ERR_OK) {
                    continue;
                }
                if (!is_uploaded_file($tmp[$idx])) {
                    continue;
                }
                if (!in_array($types[$idx], $allowedTypes, true)) {
                    continue;
                }

                $origName = basename($origName);
                $info = pathinfo($origName);
                $ext  = strtolower($info['extension'] ?? '');
                $base = $info['filename'] ?? 'image';

                $safeBase = preg_replace('/[^A-Za-z0-9._-]/', '_', $base);
                $fileName = $safeBase . '.' . $ext;
                $target   = $uploadDirFs . $fileName;

                $i = 1;
                while (file_exists($target)) {
                    $fileName = $safeBase . '_' . $i++ . '.' . $ext;
                    $target   = $uploadDirFs . $fileName;
                }

                if (move_uploaded_file($tmp[$idx], $target)) {
                    $uploadedPaths[] = $uploadDirUrl . $fileName;
                }
            }
        }

        if (!empty($uploadedPaths)) {
            $imgStmt = $pdo->prepare("
                INSERT INTO product_images (product_id, url, sort_order)
                VALUES (:pid, :url, :sort_order)
            ");
            $sort = 0;
            foreach ($uploadedPaths as $url) {
                $imgStmt->execute([
                    ':pid'        => $productId,
                    ':url'        => $url,
                    ':sort_order' => $sort++,
                ]);
            }
        }

        
        if (!empty($specs)) {
            $specStmt = $pdo->prepare("
                INSERT INTO product_specs (product_id, spec_key, spec_value, sort_order)
                VALUES (:pid, :spec_key, :spec_value, :sort_order)
            ");

            $sort = 1;
            foreach ($specs as $sp) {
                $specStmt->execute([
                    ':pid'        => $productId,
                    ':spec_key'   => $sp['key'],
                    ':spec_value' => $sp['value'],
                    ':sort_order' => $sort++,
                ]);
            }
        }

        header('Location: products.php');
        exit;
    }
}

$isCustomCategory = $category !== '' && !in_array($category, $categories, true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - RetroByte CMS</title>

    <link rel="icon" type="image/png" href="../assets/images/RetroByteLogo.png">

    <!-- styles -->
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="css/cms.css">
    <link rel="stylesheet" href="css/product-s.css">
    <link rel="stylesheet" href="css/sidebar.css">

    <!-- icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@hackernoon/pixel-icon-library/fonts/iconfont.css">

    <!-- fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Silkscreen:wght@400;700&display=swap" rel="stylesheet">

    <!-- scripts -->
    <script src="../assets/js/app.js" defer></script>
</head>
<body>
<canvas id="grid"></canvas>

<section id="cms">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>

    <div class="dashboard-container">
        <div class="form-page">
            <div class="form-header">
                <h2>Add Product</h2>
                <a href="products.php" class="pixel-button btn-ghost">← Back to Products</a>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <p><?php echo htmlspecialchars($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="post" class="cms-form" enctype="multipart/form-data">
                
                <div class="form-grid add-product-grid">
                    
                    <div class="form-group images-col">
                        <label>Images</label>

                        <div id="main-image" class="product-image-large">
                            <span class="placeholder-text">No image selected</span>
                        </div>

                        
                        <div class="images-bottom-row">
                            <div id="thumbs" class="thumbs"></div>

                            <button type="button"
                                    id="add-image-btn"
                                    class="pixel-button btn-ghost small-btn">
                                + Add Image
                            </button>
                        </div>

                        <p class="help-text">
                            Upload one or more images (PNG)
                        </p>

                        
                        <div id="image-inputs" class="image-inputs" aria-hidden="true"></div>
                    </div>

                    
                    <div class="form-group meta-col">
                        <label for="title">Title *</label>
                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="pixel-input"
                            required
                            value="<?php echo htmlspecialchars($title); ?>"
                        >

                        <label for="price" style="margin-top: 12px;">Price (€) *</label>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="price"
                            name="price"
                            class="pixel-input"
                            required
                            value="<?php echo htmlspecialchars($price); ?>"
                        >

                        <label for="year_made" style="margin-top: 12px;">Year</label>
                        <input
                            type="text"
                            id="year_made"
                            name="year_made"
                            class="pixel-input"
                            placeholder="e.g. 1998"
                            value="<?php echo htmlspecialchars($year_made); ?>"
                        >

                        <label for="category_select" style="margin-top: 12px;">Category</label>
                        <select
                            id="category_select"
                            name="category_select"
                            class="pixel-input"
                        >
                            <option value="">Select category</option>

                            <?php foreach ($categories as $catOption): ?>
                                <option
                                    value="<?php echo htmlspecialchars($catOption); ?>"
                                    <?php echo ($category === $catOption) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($catOption); ?>
                                </option>
                            <?php endforeach; ?>

                            <option
                                value="_custom_"
                                <?php echo $isCustomCategory ? 'selected' : ''; ?>
                            >
                                Other...
                            </option>
                        </select>

                        <div
                            id="category_custom_wrap"
                            class="<?php echo $isCustomCategory ? '' : 'is-hidden'; ?>"
                            style="margin-top: 8px;"
                        >
                            <input
                                type="text"
                                id="category_custom"
                                name="category_custom"
                                class="pixel-input"
                                placeholder="Custom category"
                                value="<?php echo $isCustomCategory ? htmlspecialchars($category) : ''; ?>"
                            >
                        </div>

                        <label for="tag" style="margin-top: 12px;">Tag</label>
                        <input
                            type="text"
                            id="tag"
                            name="tag"
                            class="pixel-input"
                            placeholder="HOT, RARE, SEALED..."
                            value="<?php echo htmlspecialchars($tag); ?>"
                        >

                        <label class="checkbox-label" style="margin-top: 12px; display:flex; align-items:center; gap:8px;">
                            <input
                                type="checkbox"
                                id="has_discount"
                                name="has_discount"
                                <?php echo $has_discount ? 'checked' : ''; ?>
                            >
                            Has discount price
                        </label>

                        <div id="discount-wrap" class="discount-wrap <?php echo $has_discount ? '' : 'is-hidden'; ?>">
                            <label for="discount_price" style="margin-top: 8px;">Discount Price (€)</label>
                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                id="discount_price"
                                name="discount_price"
                                class="pixel-input"
                                value="<?php echo htmlspecialchars($discount_price); ?>"
                            >
                        </div>
                    </div>
                </div>

                
                <div class="form-group">
                    <label for="short_desc">Short Description</label>
                    <textarea
                        id="short_desc"
                        name="short_desc"
                        class="pixel-input"
                        rows="3"
                    ><?php echo htmlspecialchars($short_desc); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="description">Full Description</label>
                    <textarea
                        id="description"
                        name="description"
                        class="pixel-input"
                        rows="6"
                    ><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                
                <div class="form-group">
                    <label>Specifications</label>

                    <div id="spec-list" class="spec-list">
                        <?php
                        if (!empty($spec_keys) || !empty($spec_values)) {
                            $max = max(count($spec_keys), count($spec_values));
                            for ($i = 0; $i < $max; $i++):
                                $k = htmlspecialchars($spec_keys[$i] ?? '');
                                $v = htmlspecialchars($spec_values[$i] ?? '');
                                if ($k === '' && $v === '') continue;
                        ?>
                            <div class="spec-row">
                                <input
                                    type="text"
                                    name="spec_key[]"
                                    class="pixel-input spec-key"
                                    placeholder="e.g. Condition"
                                    value="<?php echo $k; ?>"
                                >
                                <input
                                    type="text"
                                    name="spec_value[]"
                                    class="pixel-input spec-value"
                                    placeholder="e.g. Refurbished & tested"
                                    value="<?php echo $v; ?>"
                                >
                                <button type="button"
                                        class="pixel-button btn-ghost small-btn spec-remove">
                                    ✕
                                </button>
                            </div>
                        <?php
                            endfor;
                        } else {
                        ?>
                            <div class="spec-row">
                                <input
                                    type="text"
                                    name="spec_key[]"
                                    class="pixel-input spec-key"
                                    placeholder="e.g. Condition"
                                >
                                <input
                                    type="text"
                                    name="spec_value[]"
                                    class="pixel-input spec-value"
                                    placeholder="e.g. Refurbished & tested"
                                >
                                <button type="button"
                                        class="pixel-button btn-ghost small-btn spec-remove">
                                    ✕
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <button type="button"
                            id="add-spec"
                            class="pixel-button btn-ghost small-btn"
                            style="margin-top:8px;">
                        + Add Spec
                    </button>
                </div>

                <button type="submit" class="pixel-button btn-primary">
                    Save Product
                </button>
            </form>
        </div>
    </div>
</section>

<script>
function toggleTheme() {
    const r = document.documentElement;
    r.dataset.theme = r.dataset.theme === 'dark' ? '' : 'dark';
    if (r.dataset.theme) localStorage.setItem('theme','dark');
    else localStorage.removeItem('theme');
}
(function () {
    if (localStorage.getItem('theme') === 'dark') {
        document.documentElement.dataset.theme = 'dark';
    }
})();


document.addEventListener('DOMContentLoaded', function () {
    const inputsContainer = document.getElementById('image-inputs');
    const thumbsContainer = document.getElementById('thumbs');
    const mainImage       = document.getElementById('main-image');
    const addBtn          = document.getElementById('add-image-btn');

    let currentMain = null;

    function setMainFromThumb(thumb) {
        const img = thumb.querySelector('img');
        if (!img) return;
        currentMain = thumb;
        mainImage.innerHTML = '<img src="' + img.src + '" alt="">';
    }

    function updateMainAfterRemoval(removedThumb) {
        if (!thumbsContainer.children.length) {
            mainImage.innerHTML = '<span class="placeholder-text">No image selected</span>';
            currentMain = null;
            return;
        }
        if (removedThumb === currentMain) {
            const first = thumbsContainer.children[0];
            setMainFromThumb(first);
        }
    }

    function createInputAndOpenDialog() {
        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'images[]';
        input.accept = 'image/*';
        input.style.display = 'none';

        input.addEventListener('change', function () {
            if (!input.files || !input.files[0]) {
                input.remove();
                return;
            }
            const file = input.files[0];

            if (!file.type.startsWith('image/')) {
                input.remove();
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                const url = e.target.result;

                const thumb = document.createElement('button');
                thumb.type = 'button';
                thumb.className = 'thumb';

                thumb.innerHTML =
                    '<span class="thumb-remove" title="Remove image">&times;</span>' +
                    '<img src="' + url + '" alt="">';

                thumb.addEventListener('click', function (ev) {
                    if (ev.target.classList.contains('thumb-remove')) {
                        
                        thumb.remove();
                        input.remove();
                        updateMainAfterRemoval(thumb);
                    } else {
                        
                        setMainFromThumb(thumb);
                    }
                });

                thumbsContainer.appendChild(thumb);
                inputsContainer.appendChild(input);

                
                if (thumbsContainer.children.length === 1) {
                    setMainFromThumb(thumb);
                }
            };
            reader.readAsDataURL(file);
        });

        inputsContainer.appendChild(input);
        input.click();
    }

    if (addBtn) {
        addBtn.addEventListener('click', createInputAndOpenDialog);
    }
});


document.addEventListener('DOMContentLoaded', function () {
    const chk  = document.getElementById('has_discount');
    const wrap = document.getElementById('discount-wrap');

    if (chk && wrap) {
        function syncDiscount() {
            if (chk.checked) {
                wrap.classList.remove('is-hidden');
            } else {
                wrap.classList.add('is-hidden');
            }
        }
        chk.addEventListener('change', syncDiscount);
        syncDiscount();
    }

    const catSelect = document.getElementById('category_select');
    const catWrap   = document.getElementById('category_custom_wrap');

    if (catSelect && catWrap) {
        function syncCategory() {
            if (catSelect.value === '_custom_') {
                catWrap.classList.remove('is-hidden');
            } else {
                catWrap.classList.add('is-hidden');
            }
        }
        catSelect.addEventListener('change', syncCategory);
        syncCategory();
    }

  
    const specList = document.getElementById('spec-list');
    const addSpec  = document.getElementById('add-spec');

    if (specList && addSpec) {
        function addSpecRow(key = '', value = '') {
            const row = document.createElement('div');
            row.className = 'spec-row';

            function esc(str) {
                return String(str).replace(/["&<>]/g, function (ch) {
                    return ({
                        '"': '&quot;',
                        '&': '&amp;',
                        '<': '&lt;',
                        '>': '&gt;'
                    })[ch];
                });
            }

            row.innerHTML = `
                <input
                    type="text"
                    name="spec_key[]"
                    class="pixel-input spec-key"
                    placeholder="e.g. Condition"
                    value="${esc(key)}"
                >
                <input
                    type="text"
                    name="spec_value[]"
                    class="pixel-input spec-value"
                    placeholder="e.g. Refurbished &amp; tested"
                    value="${esc(value)}"
                >
                <button type="button"
                        class="pixel-button btn-ghost small-btn spec-remove">
                    ✕
                </button>
            `;
            specList.appendChild(row);
        }

        addSpec.addEventListener('click', function () {
            addSpecRow();
        });

        specList.addEventListener('click', function (e) {
            if (e.target.classList.contains('spec-remove')) {
                const row = e.target.closest('.spec-row');
                if (row && specList.children.length > 1) {
                    row.remove();
                } else if (row) {
                    row.querySelectorAll('input').forEach(inp => inp.value = '');
                }
            }
        });
    }
});
</script>

</body>
</html>
