// public/js/user-menu.js
document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("user-name");
    const menu   = document.getElementById("user-dropdown");
    const wrap   = document.getElementById("ap-user-menu");
    if (!toggle || !menu || !wrap) return;
  
    const open = () => {
      menu.classList.remove("hidden", "hiding");
      menu.classList.add("showing");
      document.addEventListener("click", outside);
      document.addEventListener("keydown", onEsc);
    };
    const close = () => {
      menu.classList.remove("showing");
      menu.classList.add("hiding");
      setTimeout(() => menu.classList.add("hidden"), 110);
      document.removeEventListener("click", outside);
      document.removeEventListener("keydown", onEsc);
    };
    const outside = (e) => { if (!wrap.contains(e.target)) close(); };
    const onEsc = (e) => { if (e.key === "Escape") close(); };
  
    toggle.addEventListener("click", (e) => {
      e.stopPropagation();
      menu.classList.contains("hidden") ? open() : close();
    });
  });
  