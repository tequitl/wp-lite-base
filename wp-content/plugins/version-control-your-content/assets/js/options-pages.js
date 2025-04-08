import { show_commits_list, get_commit_content, github_update } from './github/content-sync.js';
import { commits_box } from './github/commits-box.js';
var jq = jQuery;
jq.ajaxSetup({
	beforeSend: function(xhr) {
			xhr.setRequestHeader('X-WP-Nonce', vcyc.auth.nonce);
	}
});
var rest_path = vcyc.auth.rest_root + 'vcyc/v1/';
jQuery(document).ready(function($) {
var option_page = jq("#vcyc-options-pages-box").data("options_page");
    // Fetch and display commits list on page load
    vcyc_options_box();

		 // Handle activation/deactivation of version control
		 jq("body").on("change", "#vcyc_active", async function() {
			let isActive = this.checked ? "YES" : "NO";
			await jq.post(rest_path + "options_pages_activate_deactivate", { option_page: option_page, active: isActive });
			jq("#vcyc-active-input").val(isActive);
			sessionStorage.setItem("vcyc_active", isActive);
			vcyc_options_box();
	});


    // Fetch and display Version Control box on page load
    async function vcyc_options_box() {
        let params = await jq.getJSON(rest_path + "get_vcyc_params_options_pages", { option_page: option_page });
        sessionStorage.setItem("vcyc_commits_path", params.commits_path);
        sessionStorage.setItem("vcyc_active", params.is_active);

        jq('#vcyc-options-pages-box').html(commits_box( option_page, params));
        if (params.is_active === 'YES') {
            show_commits_list(params.commits_path);
        }
    }

   

    // Handle revert commit action
    jq("body").on("click", ".revert-commit", async function(e) {
        e.preventDefault();
        let sha = jq(this).data("sha");
        if (!confirm("Please confirm if you want to revert to this version?")) {
            return false; // Exit the function if the user cancels the confirmation
        }
       // TODO: Implement revert commit action for options pages
			 let commit_content = await get_commit_content(sha, sessionStorage.getItem("vcyc_commits_path"));
// Parse the JSON content returned from the commit
let commitData = JSON.parse(commit_content);
console.log("commitData: ", commitData);
// Fetch all form input fields and update based on commitData
		// Start of Selection
jq("form[action='options.php'] input[type='text'] , form[action='options.php'] input[type='number'], form[action='options.php'] textarea, form[action='options.php'] select, form[action='options.php'] input[type='checkbox'], form[action='options.php'] input[type='radio'] , form[action='options-permalink.php'] input[type='text'] , form[action='options-permalink.php'] select, form[action='options-permalink.php'] input[type='checkbox'], form[action='options-permalink.php'] input[type='radio']").each(function () {
    let inputField = jq(this);
    let name = inputField.attr("name");
		let tagName = inputField.prop("tagName").toLowerCase();
		let id=inputField.attr("id");
		if(id==="vcyc_active") return;
    let value = commitData[name];
		console.log(tagName, name+": "+value);
    let inputType;
  
    switch (tagName) {
        case "input":
            inputType = inputField.attr("type").toLowerCase();
            if (inputType === "button") {
                return; // Skip button input type
            }
            if (inputType === "checkbox") {
                inputField.prop("checked", commitData.hasOwnProperty(name) && value === inputField.val());
            } else if (inputType === "radio") {
                inputField.prop("checked", value === inputField.val());
            } else {
                inputField.val(value !== undefined ? value : "");
            }
            break;
        case "textarea":
            inputField.val(value !== undefined ? value : "");
            break;
        case "select":
            inputField.val(value !== undefined ? value : "").trigger("change");
            if (value !== undefined) {
                inputField.prop("disabled", false); // Remove disable attribute when setting the value
            }
            break;
        default:
            console.warn(`Unsupported input type: ${tagName}`);
    }
});

console.log("Form updated with commit data");
        return false;
    });

    // Save form data to session storage on form submission
    jq("form[action='options.php'] , form[action='options-permalink.php'] , form[action='options-privacy.php']").on("submit", async (e) => {
			//e.preventDefault();
        const form = e.currentTarget;
        const formData = new FormData(form);
        const entries = formData.entries();
        const formPostObject = Object.fromEntries(entries);
				let remove_keys = ["action","_wpnonce", "_wp_http_referer"];
				remove_keys.forEach(key => {
					if(key in formPostObject) delete formPostObject[key];
				});
				console.log("formPostObject: ", formPostObject); 

        // Only save if version control is active
        if (sessionStorage.getItem("vcyc_active") === 'YES') {
            const formJson = JSON.stringify(formPostObject, null, 2);
						
            sessionStorage.setItem("formJson", formJson);
        }
        return true;
    });

    // Sync to GitHub after page reload from session storage
    let formJson = sessionStorage.getItem("formJson");
    if (formJson && sessionStorage.getItem("vcyc_active") === 'YES') {
        if (sessionStorage.getItem("vcyc_commits_path").length > 0) {
            let commits_path = sessionStorage.getItem("vcyc_commits_path");
						let formJsonParsed = JSON.parse(formJson);
            let option_page=jq("#vcyc-options-pages-box").data("options_page");
                let message = `${vcyc.user}, ${option_page}`;
                github_update(commits_path, formJson, message);
            }
            //sessionStorage.removeItem("formJson"); // Clear sessionstorage after syncing changes to Git
        }
}); 