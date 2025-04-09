jQuery(document).ready(function($) {
  const form = $('form[action="options.php"]');

  form.on('submit', async function(e) {
    const formData = new FormData(this);
    const entries = formData.entries();
    const formPostObject = Object.fromEntries(entries);

    // Only save if version control is active
    if ($("#vcyc_active").is(":checked")) {
      const formJson = JSON.stringify(formPostObject, null, 2);
      sessionStorage.setItem("formJson", formJson);

      // Prepare data for GitHub update
      const currentDate = new Date().toLocaleString('en-US', { month: '2-digit', day: '2-digit', year: '2-digit', hour: '2-digit', minute: '2-digit', hour12: true }).replace(',', ' @');
      const username = "<?php echo wp_get_current_user()->user_login; ?>";
      const message = username + ", " + currentDate + " (Settings Update)";

      // Call function to save changes to GitHub
      await githubUpdate('settings/options-general.json', formJson, message);
    }
    return true;
  });

  // Function to sync changes to GitHub
  async function githubUpdate(filepath, content, message) {
    console.log("Updating file: ", filepath);
    const response = await fetch('/path/to/api', {
      method: 'POST',
      body: JSON.stringify({ filepath, content, message }),
      headers: { 'Content-Type': 'application/json' }
    });

    if (response.ok) {
      console.log('Settings synced to GitHub successfully.');
      sessionStorage.removeItem("formJson");
    } else {
      console.error('Error syncing settings to GitHub: ', response);
    }
  }
}); 