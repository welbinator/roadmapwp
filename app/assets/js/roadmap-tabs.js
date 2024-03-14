document.addEventListener('DOMContentLoaded', function() {
    var tabs = document.querySelectorAll('.roadmap-tab');
    var ideasContainer = document.querySelector('.roadmap-ideas-container');
    var ajaxurl = wpRoadMapAjax.ajax_url;
    var nonce = wpRoadMapAjax.nonce;

    // Function to reset all tabs to inactive
    function resetTabs() {
        tabs.forEach(function(tab) {
            tab.setAttribute('data-state', 'inactive');
        });
    }

    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            resetTabs();
            this.setAttribute('data-state', 'active');

            var status = this.getAttribute('data-status');
            loadIdeas(status);
        });
    });

    function loadIdeas(status) {
        var formData = new FormData();
        formData.append('action', 'load_ideas_for_status');
        formData.append('status', status);
        formData.append('nonce', nonce);

        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            
            if (data.success && data.data && data.data.html) {
                ideasContainer.innerHTML = data.data.html;
            } else {
                ideasContainer.innerHTML = '<p>Error: Invalid response format.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading ideas:', error);
            ideasContainer.innerHTML = '<p>Error loading ideas.</p>';
        });
    }

    if (tabs.length > 0) {
        tabs[0].click();
        tabs[0].setAttribute('data-state', 'active');
    }
});