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
                                            <img src="${notificationItem.find('img').attr('src')}" class="img-fluid mb-3" style="max-height: 300px;">
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
                        $('#notificationModal').modal('show');
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
