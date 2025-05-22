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

  // Slider Existente (Destacados)
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

    setInterval(() => {
      currentIndex =
        currentIndex === sliderItems.length - 1 ? 0 : currentIndex + 1;
      updateSlider();
    }, 5000);
  }

  // Nuevo Slider (Por Marca)
  let brandSliderItems = document.querySelectorAll(".brand-slider-item");
  let brandSliderDots = document.querySelectorAll(".brand-slider-dot");
  const brandPrevBtn = document.querySelector(".brand-prev");
  const brandNextBtn = document.querySelector(".brand-next");
  const brandFilters = document.querySelectorAll(".brand-filter");
  let brandCurrentIndex = 0;
  const itemsPerPage = 4;

  function updateBrandSlider() {
    const totalPages = Math.ceil(brandSliderItems.length / itemsPerPage);
    brandSliderItems.forEach((item, index) => {
      item.style.display = "none";
      if (
        index >= brandCurrentIndex &&
        index < brandCurrentIndex + itemsPerPage
      ) {
        item.style.display = "block";
      }
    });
    brandSliderDots.forEach((dot, index) => {
      dot.classList.toggle(
        "active",
        index === Math.floor(brandCurrentIndex / itemsPerPage)
      );
    });
    brandPrevBtn.disabled = brandCurrentIndex === 0;
    brandNextBtn.disabled =
      brandCurrentIndex >= (totalPages - 1) * itemsPerPage;
  }

  if (brandSliderItems.length > 0) {
    brandPrevBtn.addEventListener("click", () => {
      if (brandCurrentIndex > 0) {
        brandCurrentIndex -= itemsPerPage;
        updateBrandSlider();
      }
    });

    brandNextBtn.addEventListener("click", () => {
      if (brandCurrentIndex + itemsPerPage < brandSliderItems.length) {
        brandCurrentIndex += itemsPerPage;
        updateBrandSlider();
      }
    });

    brandSliderDots.forEach((dot) => {
      dot.addEventListener("click", () => {
        const dotIndex = Array.from(brandSliderDots).indexOf(dot);
        brandCurrentIndex = dotIndex * itemsPerPage;
        updateBrandSlider();
      });
    });

    brandFilters.forEach((filter) => {
      filter.addEventListener("click", (e) => {
        e.preventDefault();
        brandFilters.forEach((f) => f.classList.remove("active"));
        filter.classList.add("active");

        const brand = filter.getAttribute("data-brand");
        const filteredVehicles = brand
          ? allVehicles.filter((v) => v.brand_name === brand)
          : allVehicles;
        const brandSlider = document.querySelector(".brand-slider");
        brandSlider.innerHTML = "";

        filteredVehicles.forEach((vehicle) => {
          const item = document.createElement("div");
          item.className = "brand-slider-item";
          item.innerHTML = `
            <a href="${BASE_URL}vehicle.php?vehicle_id=${vehicle.id}">
              <img src="${BASE_URL}images/${vehicle.image}" alt="${vehicle.model_name}" class="w-full h-[200px] object-cover rounded-lg cursor-pointer transition-transform duration-300 hover:scale-105">
            </a>
            <div class="brand-slider-caption">
              <h2 class="text-1.2rem font-semibold mt-2">${vehicle.brand_name} ${vehicle.model_name}</h2>
            </div>
          `;
          brandSlider.appendChild(item);
        });

        brandSliderItems = document.querySelectorAll(".brand-slider-item");
        const totalItems = brandSliderItems.length;
        const totalDots = Math.ceil(totalItems / itemsPerPage);
        const dotsContainer = document.querySelector(".brand-slider-dots");
        dotsContainer.innerHTML = "";
        for (let i = 0; i < totalDots; i++) {
          const dot = document.createElement("span");
          dot.className = "brand-slider-dot " + (i === 0 ? "active" : "");
          dot.addEventListener("click", () => {
            brandCurrentIndex = i * itemsPerPage;
            updateBrandSlider();
          });
          dotsContainer.appendChild(dot);
        }
        brandSliderDots = document.querySelectorAll(".brand-slider-dot");

        brandCurrentIndex = 0;
        updateBrandSlider();
      });
    });

    updateBrandSlider();
    brandFilters[0].click();
  }

  // Header transparente al hacer scroll
  const header = document.getElementById("mainHeader");
  window.addEventListener("scroll", () => {
    if (window.scrollY > 20) {
      header.classList.add("scrolled");
    } else {
      header.classList.remove("scrolled");
    }
  });
  
});

// Confirmación para eliminar elementos
function confirmDelete(message) {
  return confirm(message);
}
