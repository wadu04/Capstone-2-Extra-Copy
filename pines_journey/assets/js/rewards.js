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
        $('#giveRewardModal .modal-title').text('Give Reward to ' + username);
        $('#giveRewardModal').modal('show');
    });

    // Handle reward form submission
    $('#rewardForm').submit(function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const username = $('#giveRewardModal .modal-title').text().replace('Give Reward to ', '');
        
        $.ajax({
            url: '../../ajax/give_reward.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (typeof response === 'string') {
                    try {
                        response = JSON.parse(response);
                    } catch (e) {
                        console.error('Failed to parse response:', response);
                        alert('Server error occurred');
                        return;
                    }
                }

                if (response.success) {
                    // Hide reward modal first
                    $('#giveRewardModal').modal('hide');
                    
                    // Reset form
                    $('#rewardForm')[0].reset();
                    
                    // Wait for the first modal to finish hiding
                    setTimeout(function() {
                        // Set username and show success modal
                        $('#rewarded_user').text(username);
                        $('#successModal').modal('show');
                    }, 500);
                } else {
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                }
            },
            error: function(xhr, status, error) {
                alert('Error giving reward: ' + error);
            }
        });
    });

    // Handle success modal close
    $('#successModal').on('hidden.bs.modal', function() {
        location.reload();
    });

    // Add animation when success modal shows
    $('#successModal').on('shown.bs.modal', function() {
        $('.fa-check-circle').addClass('animate__animated animate__bounceIn');
    });
});
