document.addEventListener('DOMContentLoaded', function() {
    const addForumForm = document.getElementById('addForumForm');
    const addForumModal = document.getElementById('addForumModal');
    const postsContainer = document.getElementById('postsContainer');
    const editPostModal = document.getElementById('editPostModal');

    addForumForm.addEventListener('submit', (e) => {
        e.preventDefault();

        const selectedCategories = Array.from(addForumForm.querySelectorAll('input[name="category"]:checked')).map(checkbox => checkbox.value);
        const title = addForumForm.forumTitle.value.trim();
        const content = addForumForm.forumContent.value.trim();
        const attachmentInput = document.getElementById('forumAttachment');
        const attachmentFile = attachmentInput.files[0];

        if (selectedCategories.length === 0) {
            alert('Please select at least one category.');
            return;
        }

        if (!title) {
            alert('Title is required.');
            return;
        }

        function formatDate(date) {
            const d = new Date(date);
            const year = d.getFullYear();
            const month = String(d.getMonth() + 1).padStart(2, '0'); // Months are zero-based
            const day = String(d.getDate()).padStart(2, '0');
            const hours = String(d.getHours()).padStart(2, '0');
            const minutes = String(d.getMinutes()).padStart(2, '0');
            const seconds = String(d.getSeconds()).padStart(2, '0');

            return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
        }

        const formData = new FormData();
        formData.append('forumTitle', title);
        formData.append('forumContent', content);
        formData.append('category', selectedCategories.join(', '));
        formData.append('author', 'You'); // Change this if you have user authentication
        formData.append('post_date', formatDate(new Date()));
        if (attachmentFile) {
            formData.append('attachment', attachmentFile);
        }

        fetch('create_post.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => response.json())
        .then(data => {
            console.log(data); // Log the response to see the error message
            if (data.success) {
                alert('Post created successfully!');
                addForumModal.style.display = 'none';
                addForumForm.reset();

                // Append new post to DOM
                const postId = data.post_id || Date.now(); // Use returned post_id or fallback to timestamp
                const attachment = data.attachment;
                console.log('Attachment path:', attachment);

                const postDiv = document.createElement('div');
                postDiv.className = 'post';
                postDiv.setAttribute('data-id', postId);

                // Create post actions div
                const postActionsDiv = document.createElement('div');
                postActionsDiv.className = 'post-actions';

                const postMenuSpan = document.createElement('span');
                postMenuSpan.className = 'post-menu';
                postMenuSpan.textContent = '⋮';

                const postDropdownDiv = document.createElement('div');
                postDropdownDiv.className = 'post-dropdown';
                postDropdownDiv.style.display = 'none';

                // Add edit and delete or report links
                if ('You' === 'You') { // Since author is 'You'
                    const editLink = document.createElement('a');
                    editLink.href = '#';
                    editLink.className = 'edit-post';
                    editLink.innerHTML = '<span class="menu-icon menu-icon-pen">&#9998;</span> &nbsp Edit';
                    postDropdownDiv.appendChild(editLink);

                    const deleteLink = document.createElement('a');
                    deleteLink.href = '#';
                    deleteLink.className = 'delete-post';
                    deleteLink.innerHTML = '<span class="menu-icon">&#128465;</span> Delete';
                    postDropdownDiv.appendChild(deleteLink);
                } else {
                    const reportLink = document.createElement('a');
                    reportLink.href = '#';
                    reportLink.className = 'report-post';
                    reportLink.style.color = 'red';
                    reportLink.innerHTML = '<span class="menu-icon">&#10071;</span> Report';
                    postDropdownDiv.appendChild(reportLink);
                }

                postActionsDiv.appendChild(postMenuSpan);
                postActionsDiv.appendChild(postDropdownDiv);
                postDiv.appendChild(postActionsDiv);

                // Post categories
                const postCategoriesDiv = document.createElement('div');
                postCategoriesDiv.className = 'post-categories';
                selectedCategories.forEach(cat => {
                    const span = document.createElement('span');
                    span.className = 'post-category';
                    span.textContent = cat;
                    postCategoriesDiv.appendChild(span);
                    postCategoriesDiv.appendChild(document.createTextNode(' '));
                });
                postDiv.appendChild(postCategoriesDiv);

                // Post title
                const postTitleH3 = document.createElement('h3');
                postTitleH3.className = 'post-title';
                const postTitleLink = document.createElement('a');
                postTitleLink.href = `discussion.php?post_id=${postId}`;
                postTitleLink.style.textDecoration = 'none';
                postTitleLink.style.color = 'inherit';
                postTitleLink.textContent = title;
                postTitleH3.appendChild(postTitleLink);
                postDiv.appendChild(postTitleH3);

                // Post content
                const postContentDiv = document.createElement('div');
                postContentDiv.className = 'post-content';
                postContentDiv.textContent = content;
                postDiv.appendChild(postContentDiv);

                // Add attachment if present
                if (attachment) {
                    const imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
                    const ext = attachment.split('.').pop().toLowerCase();
                    let attachmentHTML = '';
                    if (imageExtensions.includes(ext)) {
                        attachmentHTML = `<div class="post-attachment"><img src="${attachment}" alt="Attachment" style="max-width: 100%; max-height: 300px; margin-top: 10px; border-radius: 5px;"></div>`;
                    } else {
                        attachmentHTML = `<div class="post-attachment"><a href="${attachment}" target="_blank" rel="noopener noreferrer">View Attachment</a></div>`;
                    }
                    postDiv.insertAdjacentHTML('beforeend', attachmentHTML);
                }

                // Post meta
                const postMetaDiv = document.createElement('div');
                postMetaDiv.className = 'post-meta';
                const authorSpan = document.createElement('span');
                authorSpan.textContent = 'Author: You';
                const dateSpan = document.createElement('span');
                const now = new Date();
                dateSpan.textContent = `Created ${now.toISOString().slice(0, 19).replace('T', ' ')}`;
                postMetaDiv.appendChild(authorSpan);
                postMetaDiv.appendChild(dateSpan);
                postDiv.appendChild(postMetaDiv);

                // Insert new post at the top before the first post element
                const firstPost = postsContainer.querySelector('.post');
                if (firstPost) {
                    postsContainer.insertBefore(postDiv, firstPost);
                } else {
                    postsContainer.appendChild(postDiv);
                }

                // Attach event listeners for new post
                postMenuSpan.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const dropdown = this.nextElementSibling;
                    document.querySelectorAll('.post-dropdown').forEach(d => {
                        if (d !== dropdown) d.style.display = 'none';
                    });
                    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
                });

                if (postDiv.querySelector('.edit-post')) {
                    postDiv.querySelector('.edit-post').addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const postElement = e.target.closest('.post');
                        const postId = postElement.getAttribute('data-id');
                        const title = postElement.querySelector('.post-title a').textContent;
                        const content = postElement.querySelector('.post-content').textContent;
                        document.getElementById('editPostId').value = postId;
                        document.getElementById('editForumTitle').value = title;
                        document.getElementById('editForumContent').value = content;
                        editPostModal.style.display = 'block';
                    });
                }

                if (postDiv.querySelector('.delete-post')) {
                    postDiv.querySelector('.delete-post').addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        if (!confirm('Are you sure you want to delete this post?')) {
                            return;
                        }
                        const postElement = e.target.closest('.post');
                        const postId = postElement.getAttribute('data-id');
                        fetch('delete_post.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({ post_id: postId }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Post deleted successfully!');
                                postElement.remove();
                            } else {
                                alert('Failed to delete post: ' + (data.error || 'Unknown error.'));
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while deleting the post. Please try again later.');
                        });
                    });
                }

                if (postDiv.querySelector('.report-post')) {
                    postDiv.querySelector('.report-post').addEventListener('click', (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        const postElement = e.target.closest('.post');
                        currentReportPostId = postElement.getAttribute('data-id');
                        reportPostModal.style.display = 'block';
                    });
                }

                // Add click event to postDiv to redirect to discussion.php?post_id=...
                postDiv.addEventListener('click', (e) => {
                    // Prevent redirect if click is on a link or menu
                    if (e.target.tagName.toLowerCase() === 'a' || e.target.classList.contains('post-menu') || e.target.closest('.post-dropdown')) {
                        e.stopPropagation();
                        return;
                    }
                    window.location.href = `discussion.php?post_id=${postId}`;
                });

            } else {
                alert('Failed to create post: ' + (data.error || 'Unknown error.'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the post. Please try again later.');
        });
    });
});
