document.addEventListener("DOMContentLoaded", () => {
  // Dropdown Menu
  const userIcon = document.querySelector(".navbar-user img");
  const dropdownMenu = document.querySelector(".dropdown-menu");

  if (userIcon && dropdownMenu) {
    userIcon.addEventListener("click", () => {
      dropdownMenu.classList.toggle("show");
    });

    document.addEventListener("click", (e) => {
      if (!userIcon.contains(e.target) && !dropdownMenu.contains(e.target)) {
        dropdownMenu.classList.remove("show");
      }
    });
  }

  // Slider
  const slider = document.querySelector(".slider");
  const sliderItems = document.querySelectorAll(".slider-item");
  const prevButton = document.querySelector(".slider-nav .prev");
  const nextButton = document.querySelector(".slider-nav .next");
  const dots = document.querySelectorAll(".slider-dot");
  let currentIndex = 0;

  if (slider && sliderItems.length > 0) {
    const updateSlider = () => {
      slider.style.transform = `translateX(-${currentIndex * 100}%)`;
      dots.forEach((dot, index) => {
        dot.classList.toggle("active", index === currentIndex);
      });
    };

    prevButton.addEventListener("click", () => {
      currentIndex =
        currentIndex === 0 ? sliderItems.length - 1 : currentIndex - 1;
      updateSlider();
    });

    nextButton.addEventListener("click", () => {
      currentIndex =
        currentIndex === sliderItems.length - 1 ? 0 : currentIndex + 1;
      updateSlider();
    });

    dots.forEach((dot, index) => {
      dot.addEventListener("click", () => {
        currentIndex = index;
        updateSlider();
      });
    });

    // Auto-slide every 5 seconds
    setInterval(() => {
      currentIndex =
        currentIndex === sliderItems.length - 1 ? 0 : currentIndex + 1;
      updateSlider();
    }, 5000);
  }
});

function confirmDelete(message) {
  return confirm(message);
}
