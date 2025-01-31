$(document).ready(function() {
    // Handle view details button click
    $('.view-details').click(function() {
        const userId = $(this).data('user-id');
        
        // Fetch user details
        $.ajax({
            url: '../../ajax/get_user_details.php',
            type: 'POST',
            data: { user_id: userId },
            success: function(response) {
                $('#userDetailsContent').html(response);
                $('#userDetailsModal').modal('show');
            },
            error: function() {
                alert('Error fetching user details');
            }
        });
    });

    // Handle give reward button click
    $('.give-reward').click(function() {
        const userId = $(this).data('user-id');
        const username = $(this).data('username');
        
        $('#reward_user_id').val(userId);
        $('.modal-title').text('Give Reward to ' + username);
        $('#giveRewardModal').modal('show');
    });

    // Handle reward form submission
    $('#rewardForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const username = $('.modal-title').text().replace('Give Reward to ', '');
        
        $.ajax({
            url: '../../ajax/give_reward.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                const data = JSON.parse(response);
                if (data.success) {
                    // Hide reward modal
                    $('#giveRewardModal').modal('hide');
                    
                    // Reset form
                    $('#rewardForm')[0].reset();
                    
                    // Show success modal
                    $('#rewarded_user').text(username);
                    $('#successModal').modal('show');
                    
                    // Reload page after closing success modal
                    $('#successModal').on('hidden.bs.modal', function() {
                        location.reload();
                    });
                } else {
                    alert('Error: ' + data.message);
                }
            },
            error: function() {
                alert('Error giving reward');
            }
        });
    });

    // Add animation to success icon
    $('#successModal').on('shown.bs.modal', function() {
        $('.fa-check-circle').addClass('animate__animated animate__bounceIn');
    });
});
