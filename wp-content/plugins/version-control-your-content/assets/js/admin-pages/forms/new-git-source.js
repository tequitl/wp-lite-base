import { svg_icons } from './svg-icons.js';

let jq=jQuery;
let new_github_conn={};
function newGitSource() {
  // HTML structure for the form
  let form_html = 
  '<fieldset class="new-git-conn">'
    +'<h3>Add new connection</h3>'
      +'<form action="#">'
      +'<div class="field-group">'
        +'<label for="conn-name">Connection Name</label>'
        + '<input type="text" id="conn-name" placeholder="">'
      +'</div>'
      // Hide next fields initially
      +'<div class="field-group" id="git-provider-field" style="display:none;">'
        +'<label for="git-provider">Choose Git Provider </label>'
        + git_providers()
      +'</div>'
      +'<div class="github-fields"></div>'
    +'</form>'
  +'</fieldset>';

  // Append form HTML to the content wrap
  jq("main.vcyc .content-wrap .left-content").html(form_html);

  // Add event listener to show/hide git provider field based on input length
  jq("#conn-name").on("input", function() {
    if (jq(this).val().length >= 2) {
      // Show the git provider field when input length is 3 or more
      jq("#git-provider-field, .github-fields").show();
    } else {
      // Otherwise, hide it
      jq("#git-provider-field, .github-fields").hide();
    }
  });
}

function git_providers(){
  let icons_markup='';
  for(let icon_name in svg_icons){
    let icon_svg=svg_icons[icon_name];
    icons_markup+='<li class="git-provider '+icon_name+'" data-git_service="'+icon_name+'"><div class="svg-container">'+icon_svg+'</div><span class="provider-name">'+icon_name+'</span></li>';
  }
  let providers = '<ul class="git-providers">'+icons_markup+'</ul>';
  return providers;
}

function githubFields(){
  if(jq(".github-fields").children().length>0) return false;
  jq(".github-fields").append('<div class="field-group connect-with-pat">'
      +'<label for="github-pat">Github Access Token</label>'
      +'<textarea class="github-pat" placeholder=""></textarea>'
      +'<div class="vcyc-msg vcyc-info pat-or-app" style="font-style:italic;">You can either create <a href="https://github.com/settings/tokens?type=beta" target="_blank">Personal Access Token (recommended)</a>  or generate it via <a href="#" class="github-app">VCYC GitHub App</a></div></div>'
    +'<button class="connect-github"><i class="fa fa-github"></i> Connect Github</button>');
}
function displayFoundUser(user_data, pat){
  jq(".connect-with-pat, .connect-github").remove();
  let account_name=user_data.login;
  if("name" in user_data && user_data.name) account_name=user_data.name+" ("+user_data.login+")";
  jq(".github-fields").append('<div class="field-group">'
    +'<label><b>Account:</b> '+account_name+' <i class="edit-selected-account dashicons dashicons-edit"></i></label>'
    +'<label class="vcyc-msg vcyc-success fetching-repos">'+vcyc.labels.fetching_repos+'</label>'
    +'</div>');
    new_github_conn["account"]=user_data.login;
    new_github_conn["pat"]=pat;
}
function displayReposList(reposList){
  jq(".fetching-repos").remove();
  jq(".github-fields").append('<div class="field-group choose-repo-fields">'
     +'<label for="choose-repo">Choose repository</label>'
      +'<select class="choose-repo">'
        +'<option value="">Select a repository</option>'
      +'</select>'
    +'</div>');
  for(let repo of reposList){
    if(repo.private) jq(".choose-repo").append('<option value="'+repo.name+'">'+repo.full_name+'</option>');
  }
}

function displayChoosenRepo(repo_name){
  jq(".choose-repo-fields").remove();
  jq(".github-fields").append('<div class="field-group selected-repo-fields">'
    +'<label><b>Selected repository:</b> '+repo_name+' <i class="edit-selected-repo dashicons dashicons-edit"></i></label>'
    +'<label class="vcyc-msg vcyc-in-process fetching-branches">'+vcyc.labels.fetching_branches+'</label>'
  +'</div>');
  new_github_conn["repo"]=repo_name;
}
function displayBranchesList(branchesList){
  jq(".fetching-branches").remove();
  jq(".github-fields").append('<div class="field-group choose-branch-fields">'
    +'<label for="choose-branch">Choose branch</label>'
    +'<select class="choose-branch">'
      +'<option value="">Select a branch</option>'
    +'</select>'
  +'</div>');
  if(branchesList.length==0){
    jq(".choose-branch").append('<option value="main">main</option>');
  }
    else{
  for(let branch of branchesList){
    jq(".choose-branch").append('<option value="'+branch.name+'">'+branch.name+'</option>');
  }
  }
}
function displayChoosenBranch(branch_name){
  jq(".choose-branch-fields").remove();
  jq(".github-fields").append('<div class="field-group selected-branch-fields">'
    +'<label><b>Selected branch:</b> '+branch_name+' <i class="edit-selected-branch dashicons dashicons-edit"></i></label>'
  +'</div>');
  new_github_conn["branch"]=branch_name;
  console.log("new_github_conn",new_github_conn);
  
  //Show final save button
  jq(".github-fields").append('<button class="save-connection">Encrypt and Save Connection</button>');
}
jq.ajaxSetup({
  beforeSend: function(xhr) {
      xhr.setRequestHeader('X-WP-Nonce', vcyc.auth.nonce);
  }
});
function saveConnection(conn_name){
  //Save connection to db
  new_github_conn["name"]=conn_name;
  new_github_conn["id"]=new_conn_id();
  console.log("Saving connection", new_github_conn);
  jq.post(
    vcyc.auth.rest_root+"vcyc/v1/add_new_github_conn",
    {
      action: 'add_new_github_conn',
      conn: new_github_conn
    },
    function(resp){
      console.log("Response",resp);
      if("status" in resp && resp.status=="success"){
        jq(".vcyc .content-wrap .left-content").prepend('<div class="vcyc-msg vcyc-success">'+vcyc.labels.conn_save_success+'</div>');
        jq("fieldset.new-git-conn").remove();
        //Reload page
        location.reload();
      }
    });
}

function deleteConnection(conn_id){
  //Delete connection from db
  console.log("Deleting connection", conn_id);
  jq.post(
    vcyc.auth.rest_root+"vcyc/v1/delete_github_conn",
    {
      action: 'delete_github_conn',
      conn_id: conn_id
    },
    function(resp){
      console.log("Response",resp);
      if("status" in resp && resp.status=="success"){
        jq(".vcyc .content-wrap .left").prepend('<div class="success">Connection deleted successfully</div>');
        jq("fieldset.new-git-conn").remove();
        //Refresh connections list
      }
      else{
        jq(".vcyc .content-wrap .left").prepend('<div class="error">Error deleting connection</div>');
      }
    });
}


function new_conn_id() {
  const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';
  for (let i = 0; i <10; i++) {
    result += characters.charAt(Math.floor(Math.random() * characters.length));
  }
  return result;
}


export { newGitSource, githubFields, displayFoundUser, displayReposList, displayChoosenRepo, displayBranchesList, displayChoosenBranch, new_github_conn , saveConnection, deleteConnection};