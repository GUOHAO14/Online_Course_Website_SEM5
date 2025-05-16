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
