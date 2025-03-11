document.addEventListener("DOMContentLoaded", function () {
    // Sélection des éléments du DOM
    const sections = document.querySelectorAll("section");
    const header = document.getElementById("header");

    // Effet de scroll sur les sections
    window.addEventListener('scroll', function () {
        sections.forEach(section => {
            if (section.getBoundingClientRect().top < window.innerHeight * 0.8) {
                section.classList.add("visible");
            }
        });
    });

    // Effet de rétrécissement du header
    window.addEventListener("scroll", () => {
        if (window.scrollY > 50) {
            header.classList.add("shrink");
        } else {
            header.classList.remove("shrink");
        }
    });
});