$(document).ready(function() {
    function checkNotifications() {
        $.ajax({
            url: '../ajax/check_notifications.php',
            type: 'GET',
            success: function(response) {
                const data = JSON.parse(response);
                
                // Update notification badge
                if (data.unread_count > 0) {
                    $('.notification-badge').text(data.unread_count).show();
                } else {
                    $('.notification-badge').hide();
                }

                // Update notification container
                if (data.notifications.length > 0) {
                    const container = $('#notificationContainer');
                    container.empty();
                    
                    data.notifications.forEach(function(notification) {
                        const notificationHtml = `
                            <div class="notification-item ${notification.is_read ? '' : 'unread'}" data-notification-id="${notification.id}">
                                <div class="d-flex align-items-center mb-2">
                                    <img src="../${notification.image}" class="notification-image me-2" style="width: 50px; height: 50px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0">${notification.title}</h6>
                                        <small class="text-muted">${notification.created_at}</small>
                                    </div>
                                </div>
                                <p class="mb-0">${notification.description}</p>
                            </div>
                            <hr>
                        `;
                        container.append(notificationHtml);
                    });

                    // Handle notification click
                    $('.notification-item').click(function() {
                        const notificationId = $(this).data('notification-id');
                        const notificationItem = $(this);
                        
                        // Mark notification as read
                        $.ajax({
                            url: '../ajax/mark_notification_read.php',
                            type: 'POST',
                            data: { notification_id: notificationId },
                            success: function() {
                                notificationItem.removeClass('unread');
                                checkNotifications(); // Update badge count
                            }
                        });

                        // Show notification details in modal
                        const modalHtml = `
                            <div class="modal fade" id="notificationModal" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">${notificationItem.find('h6').text()}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <img src="${notificationItem.find('img').attr('src')}" class="img-fluid mb-3 notification-full-image" style="max-height: 300px; cursor: pointer;">
                                            <div class="mb-3">
                                                <a href="${notificationItem.find('img').attr('src')}" class="btn btn-primary" download>
                                                    <i class="fas fa-download"></i> Download Image
                                                </a>
                                            </div>
                                            <p>${notificationItem.find('p').text()}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        // Remove existing modal if any
                        $('#notificationModal').remove();
                        
                        // Add and show new modal
                        $('body').append(modalHtml);
                        const modal = $('#notificationModal');
                        modal.modal('show');

                        // Add click handler for the image
                        modal.find('.notification-full-image').on('click', function() {
                            const imageSrc = $(this).attr('src');
                            // Remove any existing full image modal
                            $('#fullImageModal').remove();
                            
                            // Create and append the full image modal
                            const fullImageModal = `
                                <div class="modal fade" id="fullImageModal" tabindex="-1">
                                    <div class="modal-dialog modal-xl">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-center p-0">
                                                <img src="${imageSrc}" class="img-fluid" style="max-height: 90vh;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                            
                            $('body').append(fullImageModal);
                            $('#fullImageModal').modal('show');
                        });
                    });
                } else {
                    $('#notificationContainer').html(`
                        <div class="text-center text-muted empty-notification">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p>Scan QR codes and Create a blog post in order to get reward</p>
                        </div>
                    `);
                }
            }
        });
    }

    // Check notifications on page load
    checkNotifications();

    // Check notifications every 30 seconds
    setInterval(checkNotifications, 30000);
});
