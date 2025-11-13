(() => {
  const buttons = document.querySelectorAll(".shop-filters [data-filter]");
  const cards = document.querySelectorAll(".product-card");

  function applyFilter(key) {
    cards.forEach((card) => {
      const cat = card.getAttribute("data-category");
      const show = key === "all" || cat === key;
      card.style.display = show ? "" : "none";
    });
  }

  buttons.forEach((btn) => {
    btn.addEventListener("click", () => {
      buttons.forEach((b) => {
        b.classList.remove("is-active");
        b.setAttribute("aria-selected", "false");
      });
      btn.classList.add("is-active");
      btn.setAttribute("aria-selected", "true");
      applyFilter(btn.getAttribute("data-filter"));
    });
  });
})();
