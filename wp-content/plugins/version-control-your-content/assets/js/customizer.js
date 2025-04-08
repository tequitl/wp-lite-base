import { show_commits_list, get_commit_content, github_update } from './github/content-sync.js';
import { commits_box } from './github/commits-box.js';

var jq = jQuery;
jq.ajaxSetup({
	beforeSend: function(xhr) {
			xhr.setRequestHeader('X-WP-Nonce', vcyc.auth.nonce);
	}
});
var rest_path = vcyc.auth.rest_root + 'vcyc/v1/';
jq(document).ready(function ($) {
    // Add click handler for the GitHub button
jq("body").on('click', '.vcyc-customizer-github', function() {
    if(jq(".vcyc-additional-css").length == 0) return;
    jq('.vcyc-additional-css').slideToggle();
});

jq("body").on("change", "#vcyc_active", async function() {
    let isActive = this.checked ? "YES" : "NO";
    await jq.post(rest_path + "additional_css_activate_deactivate", {active: isActive });
    sessionStorage.setItem("vcyc_active", isActive);
   show_vcyc_additional_css();
});
jq("body").on("click", ".revert-commit", async function(e) {
    e.preventDefault();
    let sha = jq(this).data("sha");
    if (!confirm("Please confirm if you want to revert to this version?")) {
        return false; // Exit the function if the user cancels the confirmation
    }
    let commit_content = await get_commit_content(sha, sessionStorage.getItem("vcyc_commits_path"));
    wp.customize.control('custom_css').setting.set(commit_content);
    //wp.customize.previewer.refresh(); // Refresh the previewer to apply changes
    return false;
});

  // Bind an event when the publish button is pressed to save CSS
  if (typeof wp.customize !== 'undefined') {

    // Bind to the additional css section
    wp.customize.section('custom_css', function(section) {
        section.container.bind('expanded', function() {
            console.log("Additional CSS Opened");
            jq(".vcyc-customizer-github").remove();
            jq(".customize-controls-close").before("<div class='vcyc-customizer-github with-tooltip bottom-tooltip' data-tooltip='View changes'>"+vcyc_customizer_github_icon()+"</div>");
            show_vcyc_additional_css();
    });
    section.container.bind('collapsed', function() {
        console.log("Additional CSS Closed");
        jq(".vcyc-customizer-github").remove();
        remove_vcyc_additional_css();
    });
    });

    // Access the 'custom_css' control dynamically
    wp.customize.control('custom_css', function (control) {
        if (control) {
            wp.customize.bind('saved', function() {
                let additionalCSS = control.setting.get();
                let message = vcyc.user + ", Additional CSS";
                if(sessionStorage.getItem("vcyc_active") === 'YES'){
                    github_update(sessionStorage.getItem("vcyc_commits_path"), additionalCSS, message);
                }
            });
        } else {
            console.error("'custom_css' control is not available.");
        }
    });
} else {
    console.error("wp.customize is not defined.");
}

});

async function show_vcyc_additional_css(){
    if(jq(".vcyc-additional-css").length == 0){
        jq("#sub-accordion-section-custom_css .description.customize-section-description").after(`<div class='vcyc-additional-css' style='display:none;'></div>`);
    }
    let parms=await jq.getJSON(rest_path+"get_vcyc_params_additional_css");
    sessionStorage.setItem("vcyc_active", parms.is_active);
    //TO DO: This path is getting mixed up wuth posts path so should be fixed by creating fallback path
    sessionStorage.setItem("vcyc_commits_path", parms.commits_path);
    jq(".vcyc-additional-css").html(commits_box("<br />Additional CSS", parms));
   
    if(sessionStorage.getItem("vcyc_active") === 'YES') show_commits_list(sessionStorage.getItem("vcyc_commits_path"));
}

function remove_vcyc_additional_css(){
    jq(".vcyc-additional-css").remove();
}

function vcyc_customizer_github_icon(){
    return '<svg class="vcyc-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" width="20" height="20"><g><path d="M438.9,146.3c0-40.9-32.2-73.1-73.1-73.1s-73.1,32.2-73.1,73.1c0,26.7,14.9,50.3,36.2,62.9v11c-0.8,18.9-8.7,35.4-22.8,50.3c-14.9,14.9-31.5,22-50.3,22.8c-30.7,0.8-54.3,5.5-73.1,16.5V136.1c21.2-12.6,36.2-35.4,36.2-62.9c0-40.9-32.2-73.1-73.1-73.1S73.1,32.2,73.1,73.1c0,26.7,14.9,50.3,36.2,62.9v239.9c-21.2,12.6-36.2,36.2-36.2,62.9c0,40.9,32.2,73.1,73.1,73.1s73.1-32.2,73.1-73.1c0-19.7-7.1-36.2-19.7-49.5c3.1-2.4,17.3-14.9,21.2-17.3c9.4-3.9,20.4-6.3,34.6-6.3c38.5-1.6,71.6-16.5,100.7-45.6c29.1-29.1,44-72.4,45.6-110.1h-0.8C423.9,195.8,438.9,173,438.9,146.3zM147.1,29.1c24.4,0,44,20.4,44,44s-20.4,44-44,44s-44-20.4-44-44S123.5,29.1,147.1,29.1zM147.1,482.1c-24.4,0-44-20.4-44-44c0-23.6,20.4-44,44-44s44,20.4,44,44C190.3,462.5,170.7,482.1,147.1,482.1zM365.7,190.3c-24.4,0-44-20.4-44-44s20.4-44,44-44s44,20.4,44,44C409.8,169.9,389.3,190.3,365.7,190.3z"/></g></svg>';
}
