"use strict";
const menu = document.querySelector(".menu-toggle");
menu?.addEventListener("click", () => {
  const opened = menu.getAttribute("aria-expanded") !== "true";
  menu.setAttribute("aria-expanded", String(opened));
  document.querySelector("#navigation").classList.toggle("open", opened);
});
const slider = document.querySelector(".slider");
if (slider) {
  const slides = [...slider.querySelectorAll(".slide")];
  const pauseButton = document.querySelector("#pause-slider");
  const reduced = matchMedia("(prefers-reduced-motion: reduce)");
  let index = 0;
  let paused = reduced.matches;
  let hovered = false;
  let focused = false;
  const show = (value) => {
    index = (value + slides.length) % slides.length;
    slides.forEach((slide, i) => {
      slide.hidden = i !== index;
    });
    document.querySelector("#slide-count").textContent =
      `${String(index + 1).padStart(2, "0")} / ${String(slides.length).padStart(2, "0")}`;
  };
  const updatePause = () => {
    pauseButton.textContent = paused ? "▷" : "Ⅱ";
    pauseButton.setAttribute(
      "aria-label",
      paused ? "Продолжить слайдер" : "Приостановить слайдер",
    );
  };
  updatePause();
  slider
    .querySelector('[data-slide="prev"]')
    .addEventListener("click", () => show(index - 1));
  slider
    .querySelector('[data-slide="next"]')
    .addEventListener("click", () => show(index + 1));
  pauseButton.addEventListener("click", () => {
    paused = !paused;
    updatePause();
  });
  slider.addEventListener("mouseenter", () => {
    hovered = true;
  });
  slider.addEventListener("mouseleave", () => {
    hovered = false;
  });
  slider.addEventListener("focusin", () => {
    focused = true;
  });
  slider.addEventListener("focusout", (event) => {
    focused = slider.contains(event.relatedTarget);
  });
  reduced.addEventListener("change", (event) => {
    paused = event.matches;
    updatePause();
  });
  setInterval(() => {
    if (!paused && !hovered && !focused && !document.hidden) show(index + 1);
  }, 3000);
}
document.querySelectorAll("[data-category]").forEach((button) => {
  button.addEventListener("click", () => {
    document
      .querySelectorAll("[data-category]")
      .forEach((item) =>
        item.setAttribute("aria-pressed", String(item === button)),
      );
    document.querySelectorAll("[data-product-category]").forEach((item) => {
      item.hidden =
        button.dataset.category !== "all" &&
        item.dataset.productCategory !== button.dataset.category;
    });
  });
});
const bouquetSelect = document.querySelector("#bouquet_id");
if (bouquetSelect) {
  const quantity = document.querySelector("#quantity");
  const updateTotal = () => {
    const price = Number(bouquetSelect.selectedOptions[0]?.dataset.price || 0);
    document.querySelector("#order-total").textContent = new Intl.NumberFormat(
      "ru-RU",
      { style: "currency", currency: "RUB", maximumFractionDigits: 0 },
    ).format(price * Number(quantity.value || 0));
  };
  bouquetSelect.addEventListener("change", updateTotal);
  quantity.addEventListener("input", updateTotal);
  updateTotal();
}
document
  .querySelector(".toast button")
  ?.addEventListener("click", () => document.querySelector(".toast").remove());
const registerForm = document.querySelector(
  'input[name="action"][value="register"]',
)?.form;
if (registerForm) {
  const login = registerForm.querySelector('[name="login"]');
  const hint = document.createElement("small");
  hint.id = "login-availability";
  hint.setAttribute("aria-live", "polite");
  login.after(hint);
  login.setAttribute(
    "aria-describedby",
    [login.getAttribute("aria-describedby"), hint.id].filter(Boolean).join(" "),
  );
  login.addEventListener("input", () => {
    login.setCustomValidity("");
    hint.textContent = "";
  });
  login.addEventListener("blur", async () => {
    if (!login.validity.valid) return;
    const checkedValue = login.value;
    try {
      const response = await fetch(
        `api.php?resource=login-available&login=${encodeURIComponent(checkedValue)}`,
      );
      const result = await response.json();
      if (login.value !== checkedValue) return;
      hint.textContent = result.available
        ? "Логин свободен"
        : "Этот логин уже занят";
      hint.className = result.available ? "muted" : "error";
      login.setCustomValidity(result.available ? "" : "Этот логин уже занят");
    } catch {
      hint.textContent = "Проверим логин при отправке формы.";
    }
  });
}
document.querySelectorAll('form[method="post"]').forEach((form) => {
  form.addEventListener("submit", () => {
    const button = form.querySelector('button[type="submit"]');
    if (button) {
      button.disabled = true;
      button.textContent = "Подождите…";
    }
  });
});
