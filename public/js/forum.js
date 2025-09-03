document.addEventListener('DOMContentLoaded', function() {
    // Like button functionality
    document.addEventListener('click', function(e) {
        if (e.target.closest('.forum-like-btn')) {
            const button = e.target.closest('.forum-like-btn');
            const type = button.dataset.type;
            const id = button.dataset.id;
            
            handleLike(button, type, id);
        }
        
        // Reply button functionality
        if (e.target.closest('.forum-reply-btn')) {
            const button = e.target.closest('.forum-reply-btn');
            const postId = button.dataset.postId;
            const userName = button.dataset.userName;
            
            handleReply(postId, userName);
        }
    });
    
    function handleLike(button, type, id) {
        const icon = button.querySelector('i');
        const countSpan = button.querySelector('.forum-like-count');
        
        // Determine the route based on type
        let url;
        if (type === 'thread') {
            url = window.forumLikeThreadRoute + '/' + id;
        } else if (type === 'post') {
            url = window.forumLikePostRoute + '/' + id;
        } else {
            return;
        }
        
        // Send AJAX request
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        })
        .then(response => {
            if (response.status === 401) {
                // Redirect to login if unauthorized
                window.location.href = '/login';
                return;
            }
            return response.json();
        })
        .then(data => {
            if (data) {
                // Update the UI
                if (data.liked) {
                    icon.classList.remove('fa-heart-o');
                    icon.classList.add('fa-heart');
                } else {
                    icon.classList.remove('fa-heart');
                    icon.classList.add('fa-heart-o');
                }
                
                countSpan.textContent = data.count;
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    function handleReply(postId, userName) {
        // Set the parent_id in the form
        const parentIdField = document.getElementById('parent_id');
        if (parentIdField) {
            parentIdField.value = postId;
        }
        
        // Scroll to the reply form
        const replyForm = document.querySelector('.forum-actions');
        if (replyForm) {
            replyForm.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
        
        // Update the textarea placeholder to show who we're replying to
        const textarea = document.querySelector('textarea[name="body"]');
        if (textarea) {
            textarea.placeholder = `Răspunzi la @${userName}...`;
            textarea.focus();
        }
        
        // Optional: Add a visual indicator
        const replyIndicator = document.createElement('div');
        replyIndicator.className = 'alert alert-info alert-sm mb-2';
        replyIndicator.innerHTML = `<i class="fas fa-reply me-2"></i>Răspunzi la <strong>@${userName}</strong> <button type="button" class="btn-close" onclick="this.parentElement.remove()"></button>`;
        
        const form = document.querySelector('.forum-actions form');
        if (form) {
            form.insertBefore(replyIndicator, form.firstChild);
        }
    }
    
    // Clear parent_id when form is submitted or reset
    const replyForm = document.querySelector('.forum-actions form');
    if (replyForm) {
        replyForm.addEventListener('submit', function() {
            // Clear the indicator after submission
            setTimeout(() => {
                const parentIdField = document.getElementById('parent_id');
                if (parentIdField) {
                    parentIdField.value = '';
                }
                
                const textarea = document.querySelector('textarea[name="body"]');
                if (textarea) {
                    textarea.placeholder = 'Scrie răspunsul tău...';
                }
            }, 100);
        });
    }
});
