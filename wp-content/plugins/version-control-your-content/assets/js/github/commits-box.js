
function commits_box(active_for, params){
    console.log("commits_box params", active_for, params);
    var git_conn = vcyc.git_conn;
    let markup;
    if (!git_conn || !("account" in git_conn) || !("name" in git_conn)) {
        var adminUrl = vcyc.admin_url + 'admin.php?page=vcyc-connections';
        markup = `
        <div class='vcyc-github-panel'>
            <div class="vcyc-control-wrapper">
                <div class='vcyc-msg vcyc-error' style="padding: 10px;">No active connection found. Please <a href='${adminUrl}'>add and activate a connection</a> for version control.</div>
            </div>
        </div>`;
        return markup;
    }
  let is_active = params.is_active === 'YES' ? "checked":"";
  let hide_class = params.is_active === 'YES' ?"":"vcyc-hide";
    markup=` <div class='vcyc-github-panel'>
      <div class="vcyc-control-wrapper">
        <div class="vcyc-control">
            <label for="vcyc_active">Active for ${active_for}</label>
            <label class="switch">
                <input type="checkbox" id="vcyc_active" ${is_active}>
                <span class="slider round"></span>
            </label>
        </div>
        <a href="#" class="refresh-commits with-tooltip ${hide_class}" data-tooltip="Refresh List">
            <i class="dashicons dashicons-update"></i>
        </a>
        <a href="${params.commit_path}" class="commits-url-link with-tooltip ${hide_class}" data-tooltip="View All" target="_blank" style="margin-right: 15px;">
            <i class="dashicons dashicons-list-view"></i>
        </a>
    </div>
    <div class="commits-wrapper"></div>
    </div>`;
    return markup;
}

export { commits_box };
