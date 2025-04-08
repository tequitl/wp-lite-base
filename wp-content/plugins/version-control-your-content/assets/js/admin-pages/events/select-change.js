import * as GitHub from "../../github/new-connection.js";

export default function SelectChange(jq, forms) {

  //Choose repository
  jq("body").on("change","select.choose-repo",async function(e){
    e.preventDefault();
    let repo_name=jq(this).val();
    if(repo_name=="") return false;
    forms.displayChoosenRepo(repo_name);
    let owner=forms.new_github_conn.account;
    //Fetch branches list
    let branches_list=await GitHub.branchesList(owner, repo_name);
    if("error" in branches_list) alert("There was error in fetching branches.");
    else{

        forms.displayBranchesList(branches_list);
      }
    return false;
  });

  //Choose branch
  jq("body").on("change","select.choose-branch",async function(e){
    let branch_name=jq(this).val();
    if(branch_name=="") return false;
    forms.displayChoosenBranch(branch_name);
  });

  


} //end of SelectChange function