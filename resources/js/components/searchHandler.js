// resources/js/components/searchHandler.js

export function initSearchHandler() {
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        let timeoutId;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(timeoutId);
            
            timeoutId = setTimeout(() => {
                const params = window.getCurrentParams();
                
                if (this.value) {
                    params.search = this.value;
                } else {
                    delete params.search;
                }
                
                window.updateUrl(params);
            }, 500);
        });
    }
}