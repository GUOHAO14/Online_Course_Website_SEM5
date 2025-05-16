const headerProfileButton = document.getElementById('headerProfileButton');
const profileContainer = headerProfileButton.parentElement;
const homepageTab = document.querySelector('.homepage_tab');
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

// Toggle profile dropdown
headerProfileButton.addEventListener('click', function () {
    profileContainer.classList.toggle('active');
    homepageTab.classList.remove('active'); // Hide homepage tab if open
});

// Toggle homepage menu
mobileMenuBtn.addEventListener('click', function () {
    homepageTab.classList.toggle('active');
    profileContainer.classList.remove('active'); // Hide profile menu if open
});

// Close profile menu when clicking outside
document.addEventListener('click', function (event) {
    if (!event.target.closest('.profile_container')) {
        profileContainer.classList.remove('active');
    }
});

// Dark Mode Toggle
document.addEventListener("DOMContentLoaded", function () {
const darkModeToggle = document.getElementById('darkModeToggle');
const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
const mobileMenuIcon = document.getElementById('mobileMenuIcon');

const setMenuIcon = (theme) => {
if (mobileMenuIcon) {
  mobileMenuIcon.src = theme === 'dark' ? 'system_img/whitemenu.png' : 'system_img/blackmenu.png';
}
};

let currentTheme = localStorage.getItem('theme') || 
                (prefersDarkScheme.matches ? 'dark' : 'light');

document.documentElement.setAttribute('data-theme', currentTheme);
setMenuIcon(currentTheme);

darkModeToggle.addEventListener('click', () => {
    currentTheme = document.documentElement.getAttribute('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    if (newTheme === 'light') {
        document.getElementById("themeText").innerHTML = "Light Mode";
    } else {
        document.getElementById("themeText").innerHTML = "Dark Mode";
    }
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    setMenuIcon(newTheme);
    });
});

// function handleResize() {
//     const moveMe = document.querySelector(".profile_container");
//     const target = document.querySelector(".homepage_tab");
//     const source = document.querySelector(".top_header");

//     if (window.innerWidth <= 387 && !target.contains(moveMe)) {
//         target.appendChild(moveMe);
//     } else if (window.innerWidth > 387 && !source.contains(moveMe)) {
//         source.appendChild(moveMe);
//     }
// }

// // Run on load and resize
// window.addEventListener("resize", handleResize);
// window.addEventListener("load", handleResize);