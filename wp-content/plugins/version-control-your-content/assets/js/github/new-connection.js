import {Octokit} from "./init.js";
let octokit=false;

async function verifyPat(pat){
  console.log("Verifying Access Token:",pat);
  octokit=new Octokit({ auth: pat });
  try{
    let user = await octokit.request('GET /user');
    if("status" in user && user.status==200 && "data" in user){
      console.log("Access Token verified successfully for user:",user.data.login);
      return user.data;
    }
  } catch(error){
    console.log("Verify Access Token Error",error);
    return {error: 'Invalid Access Token'};
  } 
}

async function reposList(){
  if(!octokit) {
    console.error("Octokit not initialized");
    return {error: 'Octokit not initialized'};
  }
  try{
    let repos = await octokit.request('GET /user/repos');
    if("status" in repos && repos.status==200 && "data" in repos){
      return repos.data;
    }
  } catch(error){
    console.log("No Privat Repos FOund",error);
    return {error: 'No repositories found'};
  }
}

async function branchesList(owner, repo){
  if(!octokit) {
    console.error("Octokit not initialized");
    return {error: 'Octokit not initialized'};
  }
  try{
    let branches = await octokit.request('GET /repos/{owner}/{repo}/branches', {
      owner: owner,
      repo: repo
    });
    if("status" in branches && branches.status==200 && "data" in branches){
      return branches.data;
    }
  } catch(error){
    console.log(error);
    return {error: 'No branches found'};
  }
}


export { verifyPat, reposList ,branchesList};