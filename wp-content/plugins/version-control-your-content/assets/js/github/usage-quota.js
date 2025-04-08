import { octokit_instance } from './init.js';
let octokit = octokit_instance();
var jq = jQuery;
var git_conn = vcyc.git_conn || false;

async function github_usage_quota() {
  if (!git_conn || !octokit || !"account" in git_conn || !"name" in git_conn) {
      return;
  }

  try {
      const response = await octokit.request("GET /rate_limit");
      if (response.status === 200) {
          const rateLimit = response.data.rate.limit;
          const rateRemaining = response.data.rate.remaining;
const resetTimeStamp = response.data.rate.reset;
const resetDate = new Date(resetTimeStamp * 1000);
const now = new Date();
const timeRemaining = resetDate - now;
const minutes = Math.floor(timeRemaining / 60000);
const seconds = Math.floor((timeRemaining % 60000) / 1000);
jq(".github-api-rate-limits").text(`${rateRemaining}/${rateLimit}`);
jq(".github-api-rate-limits-reset").text(`${minutes}m ${seconds.toString().padStart(2, '0')}s`);
      } else {
          console.error("Failed to fetch API usage. Status:", response.status);
      }
  } catch (error) {
      console.error("Error fetching API usage:", error.message);
  }
}

// Call the function to fetch the API usage on page load
github_usage_quota(); 