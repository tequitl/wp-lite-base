import { octokit_instance } from './init.js';
let octokit = octokit_instance();
var jq = jQuery;
var git_conn = vcyc.git_conn;

async function show_commits_list(commit_path, page = 1) {
  if (!git_conn || !octokit || !"account" in git_conn || !"name" in git_conn) {
      console.warn("Git connection not configured");
      return;
  }

  let filepath = commit_path;
  let git_path = "/repos/" + git_conn.account + "/" + git_conn.repo + "/commits";
  const per_page = 10; // Number of commits per page

  // Log the full GitHub URL for the file commits
  const commitsUrl = "https://github.com/" + git_conn.account + "/" + git_conn.repo + "/commits/" + git_conn.branch + "/" + filepath;
  jq(".commits-url-link").attr("href", commitsUrl);
  
  // Log the full GitHub URL for the file
  console.log("Full file URL: https://github.com/" + git_conn.account + "/" + git_conn.repo + "/blob/" + git_conn.branch + "/" + filepath);

  try {
      const response = await octokit.request("GET " + git_path, {
         sha: git_conn.branch,
         path: filepath,
         per_page: per_page,
         page: page,
         until: new Date().toISOString()
      });
      if (response.status === 200) {
        const commits = response.data;
        renderCommitMessages(commits, filepath, page, commitsUrl);
        updateGithubApiUsage(response.headers); // Update API usage
      } else {
          console.error("Failed to fetch commits. Status:", response.status);
      }
  } catch (error) {
      console.error("Error fetching commits:", error.message);
  }
}

async function get_commit_content(sha, filepath){
  if (!git_conn || !octokit || !"account" in git_conn || !"name" in git_conn || !sha || !filepath) {
      console.warn("Git connection not configured");
      return;
  }
  let git_path = "/repos/" + git_conn.account + "/" + git_conn.repo + "/contents/" + filepath;
  try {
      const response = await octokit.request("GET " + git_path + "?ref=" + sha);
      if (response.status === 200) {
          const fileData = response.data;
          
          // Step 1: Decode the base64 string to a regular string
          const decodedString = atob(fileData.content.replace(/\n/g, ''));


          // Step 2: Convert the string to a Uint8Array
          let byteArray = new Uint8Array(decodedString.split('').map(char => char.charCodeAt(0)));

          // Step 3: Create an instance of TextDecoder
          let decoder = new TextDecoder();

          // Step 4: Decode the Uint8Array back to the original string
      let originalContent = decoder.decode(byteArray);
          return originalContent;
      } else {
          console.error("Failed to fetch file data. Status:", response.status);
      }
  } catch (error) {
      console.error("Error fetching file content:", error.message);
  }
  return null;
}	

/**
 * Updates or creates a file in a GitHub repository using the GitHub API.
 * Issue: Previous implementation lacks proper error handling and has race conditions due to async/await mixing with .then()
 * 
 * @async
 * @param {string} filepath - The path to the file in the repository
 * @param {string} content - The content to be written to the file
 * @param {string} message - The commit message
 * @returns {Promise<void>}
 * 
 * @throws {Error} When git connection is not properly configured
 * @throws {Error} When GitHub API request fails
 * 
 * @requires octokit - The Octokit instance for GitHub API calls
 * @requires git_conn - The git connection configuration object containing account and repo information
 * 
 * @example
 * await github_update('path/to/file.txt', 'file content', 'Update file content');
 */
async function github_update(filepath, content, message) {
  let current_datetime = new Date().toLocaleString('en-US', {
    month: '2-digit',
    day: '2-digit',
    year: '2-digit',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true
  }).replace(',', ' @');
  message = message + ", " + current_datetime;

  console.log("Updating file: ", filepath);
  
  if(!git_conn || !octokit || !"account" in git_conn || !"name" in git_conn){ 
      console.warn("Git connection not configured");
      return; 
  }
  let git_path = "/repos/" + git_conn.account + "/" + git_conn.repo + "/contents/" + filepath + "?ref=" + git_conn.branch;

  //const base64_string = btoa(content);
  let encoder = new TextEncoder();
  let base64_string = btoa(String.fromCharCode(...encoder.encode(content)));
  let update_params = {
    message: message,
    content: base64_string,
    branch: git_conn.branch
  };
  let new_file = false, content_same = true;

  try {
    const get_previous_version = await octokit.request("GET " + git_path);
    if (get_previous_version.status === 200) {
      // Previous version returned so lets update it
      let pv_data = get_previous_version.data;
      let sha = pv_data.sha;
      update_params.sha = sha;
      let pv_content = pv_data.content.replace(/[\r\n]/g, '');
      content_same = compare_content(pv_content, base64_string);
      console.log("Content is same: ", content_same);
    } else {
      new_file = true;
      console.error("No previous version. Creating new file");
    }
    updateGithubApiUsage(get_previous_version.headers); // Update API usage
  } catch (e) {
    if (e.status === 404) {
      new_file = true;
      console.log("No previous version. Creating new file");
    } else {
      console.error("Error fetching previous version:", e.message);
    }
  }

  if (!content_same || new_file) {
    try {
      const octokit_response = await octokit.request("PUT " + git_path, update_params);
      if (octokit_response.status === 200 || octokit_response.status === 201) {
        console.log("Content synced to Github successfully.");
        
        // Prepend new commit to the commits list
        const commitUrl = octokit_response.data.commit.html_url;
        let commit = octokit_response.data.commit;
        //console.log("Commit: ", commit);
        // Append the new commit to the list
        const newCommitHtml = renderCommitMessage(commit, filepath);
        jq("li.no-versions-yet").remove();
        jq(".commits-list").prepend(newCommitHtml);
      } else {
        console.error("Error syncing content to Github:", octokit_response);
      }
      updateGithubApiUsage(octokit_response.headers); // Update API usage
    } catch (error) {
      console.error("Error syncing content to Github:", error.message);
    }
  }
  // Clear the formJson from sessionStorage after successful sync to Github
  sessionStorage.removeItem("formJson");
}

function compare_content(str1, str2) {
	// Length check
	if (str1.length !== str2.length) {
			return false;
	}

	// Use a larger chunk size for better performance
	const chunkSize = 8192;
	for (let i = 0; i < str1.length; i += chunkSize) {
			const chunk1 = str1.slice(i, i + chunkSize);
			const chunk2 = str2.slice(i, i + chunkSize);

			// Early return if any chunk differs
			if (chunk1 !== chunk2) {
					return false;
			}
	}

	// If no differences were found, return true
	return true;
}

// Function to render commit messages and actions with pagination
function renderCommitMessages(commits, filepath, page, commitsUrl) {
  let commitMessages = '<ul class="commits-list">';
  
  // Iterate over each commit and generate the HTML markup
  commits.forEach(commit => {
    commitMessages +=renderCommitMessage(commit, filepath);
  });
  if(commits.length === 0){
    commitMessages += "<li class='no-versions-yet'>No versions available.</li>";
  }
  else{
    jq("li.no-versions-yet").remove();
  }
  
  commitMessages += "</ul>";

  // Add pagination controls and commits URL link
  commitMessages += `<div class="pagination-controls">
    <button class="button pagination-button" data-page="${page - 1}" ${page === 1 ? 'disabled' : ''}><i class="dashicons dashicons-arrow-left-alt2" style="margin-top: 5px;"></i> Previous</button>
    <button class="button pagination-button" data-page="${page + 1}">Next <i class="dashicons dashicons-arrow-right-alt2" style="margin-top: 5px;"></i></button>
  </div>`;

  // Clear the existing commit list and append the new one
  jq(".commits-list, .pagination-controls").remove();
  if (jq(".commits-wrapper").length > 0) {
    jq(".commits-wrapper").html(commitMessages);
  }

  // Add event listeners to pagination buttons
  document.querySelectorAll('.pagination-button').forEach(button => {
    button.addEventListener('click', function(event) {
      event.preventDefault();
      const page = parseInt(this.getAttribute('data-page'));
      show_commits_list(filepath, page);
    });
  });
}

function renderCommitMessage(commit, filepath){
  let msg = "Commit";
    if ("commit" in commit) msg = commit.commit.message;
    else msg = commit.message;
  const commitUrl = commit.html_url;
  return `<li>
  <a href="${commitUrl}" target="_blank" onclick="window.open('${commitUrl}', 'popup', 'width=1024,height=768'); return false;">${msg}</a>
  <div class="action-buttons">
    <button class="view-commit with-tooltip" data-tooltip="View changes" onclick="window.open('${commitUrl}', 'popup', 'width=1024,height=768'); return false;"><i class="dashicons dashicons-visibility"></i></button>
    <button class="revert-commit with-tooltip" data-tooltip="Revert to this version" data-sha="${commit.sha}">
      <i class="dashicons dashicons-undo"></i>
    </button>
  </div>
</li>`
}

function updateGithubApiUsage(headers) {
  const rateLimit = headers['x-ratelimit-limit'];
  const rateRemaining = headers['x-ratelimit-remaining'];
  const apiUsage = `${rateRemaining}/${rateLimit}`;
  jq(".github-api-rate-limits").text(apiUsage);
  const resetTimeStamp = headers['x-ratelimit-reset'];
  const resetDate = new Date(resetTimeStamp * 1000);
  const now = new Date();
  const timeRemaining = resetDate - now;
  const minutes = Math.floor(timeRemaining / 60000);
  const seconds = Math.floor((timeRemaining % 60000) / 1000);
  const resetTime = `${minutes}m ${seconds.toString().padStart(2, '0')}s`;
  // Update the reset time in the UI
  jq(".github-api-rate-limits-reset").text(resetTime);
}

export { show_commits_list, get_commit_content, github_update };