const canvas = document.getElementById("grid");
const ctx = canvas.getContext("2d", { alpha: true });

function cssVar(name) {
  return getComputedStyle(document.documentElement)
    .getPropertyValue(name)
    .trim();
}

function resizeCanvas() {
  const dpr = Math.max(1, window.devicePixelRatio || 1);

  const w = window.innerWidth;
  const h = window.innerHeight;

  canvas.style.width = w + "px";
  canvas.style.height = h + "px";

  canvas.width = Math.floor(w * dpr);
  canvas.height = Math.floor(h * dpr);

  ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
  drawGrid();
}

function drawGrid() {
  const size = 28;
  const color = cssVar("--grid-color") || "rgba(0,0,0,.2)";

  const w = canvas.clientWidth;
  const h = canvas.clientHeight;

  const offsetX = (window.scrollX || 0) % size;
  const offsetY = (window.scrollY || 0) % size;

  ctx.clearRect(0, 0, w, h);
  ctx.strokeStyle = color;
  ctx.lineWidth = 1;

  const o = 0.5;

  for (let x = -offsetX; x <= w; x += size) {
    const xx = Math.floor(x) + o;
    ctx.beginPath();
    ctx.moveTo(xx, 0);
    ctx.lineTo(xx, h);
    ctx.stroke();
  }

  for (let y = -offsetY; y <= h; y += size) {
    const yy = Math.floor(y) + o;
    ctx.beginPath();
    ctx.moveTo(0, yy);
    ctx.lineTo(w, yy);
    ctx.stroke();
  }
}

let ticking = false;
window.addEventListener(
  "scroll",
  () => {
    if (!ticking) {
      requestAnimationFrame(() => {
        drawGrid();
        ticking = false;
      });
      ticking = true;
    }
  },
  { passive: true }
);

window.addEventListener("resize", resizeCanvas);

new MutationObserver((muts) => {
  for (const m of muts) {
    if (m.attributeName === "data-theme") {
      drawGrid();
      break;
    }
  }
}).observe(document.documentElement, { attributes: true });

resizeCanvas();

// Animations

(function () {
  const prefersReduced = window.matchMedia(
    "(prefers-reduced-motion: reduce)"
  ).matches;
  const allReveal = Array.from(
    document.querySelectorAll("[data-reveal], [data-stagger]")
  );
  if (!allReveal.length) return;

  // Αν reduced motion, δείξε αμέσως
  if (prefersReduced) {
    allReveal.forEach((el) => el.classList.add("is-visible"));
    return;
  }

  // Προετοιμασία delays & stagger
  document.querySelectorAll("[data-reveal]").forEach((el) => {
    const baseDelay = Number(el.getAttribute("data-reveal-delay") || 0);
    el.style.setProperty("--reveal-delay", baseDelay + "ms");
  });

  // Για containers με stagger: δώσε index & βήμα στα παιδιά
  document.querySelectorAll("[data-stagger]").forEach((container) => {
    const step = Number(container.getAttribute("data-stagger-step") || 100);
    container.style.setProperty("--stagger-step", step + "ms");
    Array.from(container.children).forEach((child, idx) => {
      child.style.setProperty("--stagger-index", idx);
    });
  });

  const options = {
    root: null,
    rootMargin:
      document.body.getAttribute("data-reveal-root-margin") ||
      "0px 0px -10% 0px",
    threshold: 0.12,
  };

  const onceDefault = true;

  const io = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      const el = entry.target;
      const isOnce = el.hasAttribute("data-reveal-once")
        ? el.getAttribute("data-reveal-once") !== "false"
        : onceDefault;

      // όταν το container με data-stagger εμφανιστεί, κάνε visible και τα παιδιά διαδοχικά
      if (el.hasAttribute("data-stagger")) {
        if (entry.isIntersecting) {
          el.classList.add("is-visible");
          Array.from(el.children).forEach((child) =>
            child.classList.add("is-visible")
          );
          if (isOnce) io.unobserve(el);
        } else if (!isOnce) {
          el.classList.remove("is-visible");
          Array.from(el.children).forEach((child) =>
            child.classList.remove("is-visible")
          );
        }
        return; // skip παρακάτω
      }

      // μεμονωμένα reveals
      if (entry.isIntersecting) {
        el.classList.add("is-visible");
        if (isOnce) io.unobserve(el);
      } else if (!isOnce) {
        el.classList.remove("is-visible");
      }
    });
  }, options);

  allReveal.forEach((el) => io.observe(el));
})();
