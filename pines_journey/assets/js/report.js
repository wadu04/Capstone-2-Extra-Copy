function submitReport(contentType, contentId) {
    // Get the report form data
    const reportType = document.querySelector(`#reportType_${contentType}_${contentId}`).value;
    const description = document.querySelector(`#reportDescription_${contentType}_${contentId}`).value;

    // Create form data
    const formData = new FormData();
    formData.append('content_type', contentType);
    formData.append('content_id', contentId);
    formData.append('report_type', reportType);
    formData.append('description', description);

    // Submit the report
    fetch('/pines_journey/includes/submit_report.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Close the report modal
            const modal = document.querySelector(`#reportModal_${contentType}_${contentId}`);
            const bootstrapModal = bootstrap.Modal.getInstance(modal);
            bootstrapModal.hide();

            // Show success message
            alert('Report submitted successfully. Our admin team will review it.');

            // Clear the form
            document.querySelector(`#reportType_${contentType}_${contentId}`).value = '';
            document.querySelector(`#reportDescription_${contentType}_${contentId}`).value = '';
        } else {
            alert('Error submitting report: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error submitting report. Please try again.');
    });
}
