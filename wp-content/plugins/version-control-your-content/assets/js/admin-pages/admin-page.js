import forms from './forms/forms-loader.js';
import eventsLoader from './events/events-loader.js';	
import { octokit_instance } from '../github/init.js';

//Main admin page
let jq=jQuery;
let dynamic_container=jq(".vcyc .content-wrap .connections-container");
let new_git_src_btn='';
jq(document).ready(function(){
	      
	
	/*Tempporary: Show form by default
	forms.newGitSource();
    jq("#git-provider-field, .github-fields").show();
    forms.githubFields();
    //*/

	//Load all form events
	eventsLoader(jq, forms);

	
	//Add listeners for buttons clicks
	switch(pagenow){
		case 'options-general':
			jq("input[type='submit'].button.button-primary").after("WPVC");
			break;
	}

});

let octokit = octokit_instance();
var git_conn = vcyc.git_conn;

async function fetchGithubApiUsage() {
  if (!git_conn || !octokit || !"account" in git_conn || !"name" in git_conn) {
      console.warn("Git connection not configured");
      return;
  }

  try {
      const response = await octokit.request("GET /rate_limit");
      if (response.status === 200) {
          const rateLimit = response.data.rate.limit;
          const rateRemaining = response.data.rate.remaining;
          const apiUsage = `${rateRemaining}/${rateLimit}`;
          jq('#wp-admin-bar-github_api_usage > .ab-item').text('Github API Usage: ' + apiUsage);
      } else {
          console.error("Failed to fetch API usage. Status:", response.status);
      }
  } catch (error) {
      console.error("Error fetching API usage:", error.message);
  }
}

// Call the function to fetch the API usage on page load
fetchGithubApiUsage();


//*/


