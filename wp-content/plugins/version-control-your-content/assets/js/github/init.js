//Import Octokit Library
import { Octokit } from "https://esm.sh/@octokit/core";

// Function to initialize Octokit with token from window variable
function octokit_instance() {
    
    if(typeof vcyc!='undefined' && typeof vcyc.git_conn !== 'undefined' && "pat" in vcyc.git_conn){  
        return new Octokit({ auth: vcyc.git_conn.pat });
    }
    //console.error("GitHub connection params not found"); 
    return false;
}

// Export both Octokit and octokit_instance
export { Octokit, octokit_instance };