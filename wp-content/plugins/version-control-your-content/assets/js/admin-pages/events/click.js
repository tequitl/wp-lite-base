import * as GitHub from "../../github/new-connection.js";
export default function Click(jq, forms) {

   
// Event listener for GitHub App link click
jq("body").on("click", ".github-app", function(e) {
  e.preventDefault();
  window.open("https://vcyc.harisamjed.pro/github-authentication/", '_blank', 'width=800,height=600');
  return false;
});
  //Click on Add new connection button
  jq("body").on("click",".connection-card.add-new",async (e)=>{
		e.preventDefault();
		forms.newGitSource();
		return false;
	});

  //Click on Git services providers list
  jq("body").on("click","li.git-provider",function(e){
    e.preventDefault();
    if(jq(this).hasClass("GitHub")){
      jq(this).addClass("selected");
      forms.githubFields();
    } else{
      alert(jq(this).data("git_service")+" support will be added in upcoming versions.");
    } 
    
    return false;
  });

  async function fetch_and_display_repos(){
    let reposList=await GitHub.reposList();
				if("error" in reposList) {
					jq(".github-pat").after('<div class="pat-error">No private repositories found. Please create one and try again.</div>');
				} else {
      forms.displayReposList(reposList);
    }
  }
  async function fetch_and_display_branches(){
    let branchesList=await GitHub.branchesList(forms.new_github_conn.account, forms.new_github_conn.repo);
    forms.displayBranchesList(branchesList);
  }
  //Click on Connect Github button
	jq("body").on("click",".connect-github",async function(e){
		e.preventDefault();
		let pat=jq("textarea.github-pat").val();
		
		jq(".pat-error").remove(); // Remove any previous error messages
		if(pat.length < 30) {
			jq(".github-pat").after('<div class="vcyc-msg vcyc-error">Invalid Access Token format. Please enter a valid Access Token.</div>');
		} else {
			//Verify Access Token
      jq(".github-pat").after('<div class="pat-verify vcyc-msg vcyc-in-process">'+vcyc.labels.verifying_pat+'</div>');
			let pat_user=await GitHub.verifyPat(pat);
      jq(".pat-verify").remove();
			if("error" in pat_user) {
				jq(".github-pat").after('<div class="vcyc-msg vcyc-error pat-error">'+vcyc.labels.pat_invalid+'</div>');
			} else {
				//Display found user
				forms.displayFoundUser(pat_user, pat);
				
				//Fetch User Repos and display
        fetch_and_display_repos();
				
			}
		}
		return false;
	});

   //Add event listener to edit selected account
   jq("body").on("click", ".edit-selected-account", function() {
    jq(".github-fields").empty();
    forms.githubFields();
    return false;
  });

  //Add event listener to edit selected repository
  jq("body").on("click", ".edit-selected-repo", function() {
    jq(".selected-repo-fields, .selected-branch-fields, .choose-branch-fields, .save-connection").remove();
    //Fetch User Repos and display
    fetch_and_display_repos();
    return false;
  });

  //Add event listener to edit selected branch
  jq("body").on("click", ".edit-selected-branch", function() {
    jq(".selected-branch-fields, .save-connection").remove();
    //Fetch Repository Branches and display
    fetch_and_display_branches();
    return false;
  });
  
  //Activate/Deactivate connection
  jq("body").on("click",".active-connection",async function(e){

    let conn_id=jq(this).data("id");
    jq.ajaxSetup({
      beforeSend: function(xhr) {
          xhr.setRequestHeader('X-WP-Nonce', vcyc.auth.nonce);
      }
    });
    if (jq(this).is(':checked')) {

       // Uncheck all other checkboxes and trigger their change event
      jq('.active-connection').not(this).prop('checked', false).trigger('change');
      jq.post(vcyc.auth.rest_root+"vcyc/v1/activate_connection",{conn_id: conn_id},function(resp){
          if("status" in resp && resp.status=="success"){
            console.log(vcyc.labels.conn_activate_success+":"+conn_id);
            //jq(".vcyc .content-wrap .left-content").find(".success-msg").remove();
            //jq(".vcyc .content-wrap .left-content").prepend('<div class="success-msg">Connection activated successfully</div>');
            jq(".vcyc .content-wrap .left-content").find(".no-connection").remove();
          
          }});
    } else{
      //Logic to remove active connection
      jq.post(vcyc.auth.rest_root+"vcyc/v1/deactivate_connection",{conn_id: conn_id},function(resp){
          if("status" in resp && resp.status=="success"){
            console.log("Connection removed successfully:"+conn_id);
           // jq(".vcyc .content-wrap .left-content").prepend('<div class="success-msg">Connection deactivated!</div>');
     
          }});

      // Check if no other connections are active and show error message
      if (jq('.active-connection:checked').length === 0) {
        jq(".vcyc .content-wrap .left-content").find(".no-connection").remove();
        jq(".vcyc .content-wrap .left-content").prepend('<div class="no-connection vcyc-msg vcyc-error">'+vcyc.labels.no_conn_error+'</div>');
      } else{
        jq(".vcyc .content-wrap .left-content").find(".no-connection").remove();
      }
      
    }
    
  });



  jq("body").on("click",".save-connection",function(e){
    e.preventDefault();
    let conn_name=jq("#conn-name").val();
    if(conn_name.length<2){
      alert("Please enter at least 2 digit connection name.");
      return false;
    }
    forms.saveConnection(conn_name);
    return false;
  });

  jq("body").on("click",".delete-connection",function(e){
    e.preventDefault();
    let name=jq(this).data("name");
    let id=jq(this).data("id");
    let conf=confirm("Are you sure you want to delete connection '"+name+"' ?");
    if(conf){
      jq(this).parents(".connection-card").css("background", "red").fadeOut(300);
      forms.deleteConnection(id);
    }
    return false;
  });

} //end of Click function