// Forum JavaScript - Enhanced UI Interactions
document.addEventListener('DOMContentLoaded', function() {
    // Debounce helper function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Get DOM elements
    const searchInput = document.querySelector('.forum-search input');
    const categoryPills = document.querySelectorAll('.forum-category-pill');
    const threadCards = document.querySelectorAll('.forum-thread-card');
    const sortSelect = document.querySelector('.forum-sort select');
    
    // Current filter state
    let currentFilters = {
        search: '',
        category: 'Toate',
        sortBy: 'latest'
    };

    // Filter threads based on current filters
    function filterThreads() {
        threadCards.forEach((card, index) => {
            const title = card.querySelector('.forum-thread-title a').textContent.toLowerCase();
            const excerpt = card.querySelector('.forum-thread-excerpt').textContent.toLowerCase();
            const category = card.getAttribute('data-category');
            const searchText = currentFilters.search.toLowerCase();
            
            // Check search filter
            const matchesSearch = !currentFilters.search || 
                title.includes(searchText) || 
                excerpt.includes(searchText);
            
            // Check category filter
            const matchesCategory = currentFilters.category === 'Toate' || 
                category === currentFilters.category;
            
            // Show/hide card
            if (matchesSearch && matchesCategory) {
                card.style.display = 'block';
                // Add fade-in animation
                card.classList.add('forum-fade-in');
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            } else {
                card.style.display = 'none';
                card.classList.remove('forum-fade-in');
            }
        });
        
        // Update URL without reload
        updateURL();
    }

    // Sort threads based on current sort
    function sortThreads() {
        const cardsArray = Array.from(threadCards);
        const container = cardsArray[0]?.parentElement;
        
        if (!container) return;
        
        cardsArray.sort((a, b) => {
            if (currentFilters.sortBy === 'latest') {
                // Sort by last activity time (simplified - using DOM order for demo)
                return 0;
            } else if (currentFilters.sortBy === 'replies') {
                const repliesA = parseInt(a.querySelector('.forum-stat[data-stat="replies"] .forum-stat-value')?.textContent || '0');
                const repliesB = parseInt(b.querySelector('.forum-stat[data-stat="replies"] .forum-stat-value')?.textContent || '0');
                return repliesB - repliesA;
            }
            return 0;
        });
        
        // Re-append sorted cards
        cardsArray.forEach(card => container.appendChild(card));
    }

    // Update URL with current filters
    function updateURL() {
        const params = new URLSearchParams();
        if (currentFilters.search) params.set('search', currentFilters.search);
        if (currentFilters.category !== 'Toate') params.set('category', currentFilters.category);
        if (currentFilters.sortBy !== 'latest') params.set('sort', currentFilters.sortBy);
        
        const newURL = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
        history.replaceState(null, '', newURL);
    }

    // Load filters from URL on page load
    function loadFiltersFromURL() {
        const params = new URLSearchParams(window.location.search);
        const search = params.get('search') || '';
        const category = params.get('category') || 'Toate';
        const sort = params.get('sort') || 'latest';
        
        if (search) {
            searchInput.value = search;
            currentFilters.search = search;
        }
        
        if (category !== 'Toate') {
            currentFilters.category = category;
            updateCategoryPills();
        }
        
        if (sort !== 'latest') {
            currentFilters.sortBy = sort;
            if (sortSelect) sortSelect.value = sort;
        }
        
        filterThreads();
        sortThreads();
    }

    // Update category pills active state
    function updateCategoryPills() {
        categoryPills.forEach(pill => {
            if (pill.textContent.trim() === currentFilters.category) {
                pill.classList.add('active');
            } else {
                pill.classList.remove('active');
            }
        });
    }

    // Search input event listener with debouncing
    if (searchInput) {
        searchInput.addEventListener('input', debounce(function() {
            currentFilters.search = this.value;
            filterThreads();
        }, 300));
    }

    // Category pill event listeners (now they're links, so just handle search)
    // The category filtering is now handled by the server via routes

    // Sort select event listener
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            currentFilters.sortBy = this.value;
            sortThreads();
            updateURL();
        });
    }

    // Add fade-in animation to cards on load
    function animateCardsOnLoad() {
        threadCards.forEach((card, index) => {
            card.classList.add('forum-fade-in');
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 100);
        });
    }

    // Initialize
    function init() {
        loadFiltersFromURL();
        animateCardsOnLoad();
        updateCategoryPills();
    }

    // Run initialization
    init();

    // Add keyboard navigation for accessibility
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            // Clear search on escape
            if (searchInput && searchInput === document.activeElement) {
                searchInput.value = '';
                currentFilters.search = '';
                filterThreads();
            }
        }
    });

    // Add smooth scrolling for category pills on mobile
    if (window.innerWidth <= 768) {
        categoryPills.forEach(pill => {
            pill.addEventListener('click', function() {
                // Smooth scroll to make sure the pill is visible
                this.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest',
                    inline: 'center'
                });
            });
        });
    }

    // Performance optimization: Throttle scroll events
    let ticking = false;
    function updateOnScroll() {
        if (!ticking) {
            requestAnimationFrame(() => {
                // Add any scroll-based animations here if needed
                ticking = false;
            });
            ticking = true;
        }
    }

    // Add scroll listener for potential future scroll animations
    window.addEventListener('scroll', updateOnScroll, { passive: true });

    // Add intersection observer for lazy loading animations (future enhancement)
    if ('IntersectionObserver' in window) {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '50px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        // Observe thread cards for future lazy loading
        threadCards.forEach(card => {
            observer.observe(card);
        });
    }
});
