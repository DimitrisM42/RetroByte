(function () {
  const qs = (sel, ctx = document) => ctx.querySelector(sel);
  const qsa = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const filterBar = qs(".shop-filters");
  const cards = qsa(".product-card");

  if (!filterBar || !cards.length) return;

  const setActive = (filter) => {
    qsa("[data-filter]", filterBar).forEach((btn) => {
      const isActive =
        btn.dataset.filter === filter ||
        (filter === "all" && btn.dataset.filter === "all");
      btn.classList.toggle("is-active", isActive);
      btn.setAttribute("aria-selected", isActive ? "true" : "false");
    });
  };

  const applyFilter = (filter) => {
    const f = (filter || "all").toLowerCase();
    cards.forEach((card) => {
      const cat = (card.dataset.category || "").toLowerCase();
      const show = f === "all" || cat === f;
      card.classList.toggle("is-hidden", !show);
    });
    setActive(f);

    const url = new URL(window.location.href);
    if (f === "all") {
      url.searchParams.delete("cat");
    } else {
      url.searchParams.set("cat", f);
    }
    window.history.replaceState({}, "", url.toString());
  };

  const params = new URLSearchParams(window.location.search);
  const initial = (params.get("cat") || "all").toLowerCase();
  applyFilter(initial);

  filterBar.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-filter]");
    if (!btn) return;
    e.preventDefault();
    applyFilter(btn.dataset.filter);
  });
})();
