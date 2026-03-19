class DisableBackButton {
    constructor() {
        this.init();
    }

    init() {
        // Push a new state to history
        history.pushState(null, null, location.href);
        // Listen for popstate event (back button)
        window.addEventListener('popstate', (event) => {
            history.pushState(null, null, location.href);
            alert('Back button is disabled on this page.');
        });

        // Prevent clicks on logout/back links
        document.addEventListener('click', (event) => {
            const target = event.target.closest('a');
            if (target && (target.href.includes('dep.php') || target.href.includes('staffdep.php'))) {
                event.preventDefault();
                alert('Logout/back navigation is disabled on this page.');
            }
        });
    }
}

// Instantiate the class
new DisableBackButton();
