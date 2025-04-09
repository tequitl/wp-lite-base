import { commits_box } from './github/commits-box.js';
import { show_commits_list, get_commit_content, github_update } from './github/content-sync.js';

var jq = jQuery;
jq.ajaxSetup({
	beforeSend: function(xhr) {
			xhr.setRequestHeader('X-WP-Nonce', vcyc.auth.nonce);
	}
});
var rest_path = vcyc.auth.rest_root + 'vcyc/v1/';
jQuery(document).ready(function($) {

	// Fetch and display Version Control box on page load
	vcyc_meta_box();

	jq("body").on("change", "#vcyc_active", async function() {
		let post_id = jq('#post_ID').val();
		let isActive = this.checked ? "YES" : "NO";
		try {
			await jq.post(rest_path + "post_activate_deactivate", { post_id: post_id, active: isActive });
			jq("#vcyc-active-input").val(isActive);
			sessionStorage.setItem("vcyc_active", isActive);
			vcyc_meta_box();
		} catch (error) {
			console.error("Error activating/deactivating post:", error);
		}
	});

	async function vcyc_meta_box() {
		let post_id = jq('#post_ID').val();
		if (!post_id || post_id === '') {
			console.warn("No post ID found");
			return;
		}
		try {
			let parms = await jq.getJSON(rest_path + "get_vcyc_params_meta_box", { post_id: post_id });
			sessionStorage.setItem("vcyc_commits_path", parms.commits_path);
			sessionStorage.setItem("vcyc_active", parms.is_active);

			jq('#vcyc-meta-box').html(commits_box("this " + parms.post_type, parms));
			if (parms.is_active === 'YES') {
				show_commits_list(parms.commits_path);
			}
		} catch (error) {
			console.error("Error fetching meta box parameters:", error);
		}
	}

	jq("body").on('click', '.refresh-commits', function(event) {
		event.preventDefault();
		let commitsPath = sessionStorage.getItem("vcyc_commits_path");
		if (commitsPath) {
			show_commits_list(commitsPath);
		} else {
			console.warn("No commits path found in session storage");
		}
	});

	jq("body").on("click", ".revert-commit", async function(e) {
		e.preventDefault();
		let sha = jq(this).data("sha");
		if (!confirm("Please confirm if you want to revert Editor content to this version?")) {
			return false; // Exit the function if the user cancels the confirmation
		}
		try {
			let commit_content = await get_commit_content(sha, sessionStorage.getItem("vcyc_commits_path"));
			const textArea = document.querySelector('.wp-editor-area');
			if (textArea) {
				textArea.value = commit_content;
			} else if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
				wp.data.dispatch('core/editor').editPost({
					content: commit_content
				});
			} else {
				console.error("Textarea with class .wp-editor-area not found.");
			}
		} catch (error) {
			console.error("Error reverting commit:", error);
		}
		return false;
	});

	// Post Editor Save form data to session storage
	jq("form[action='post.php']#post").on("submit", async (e) => {
		const form = e.currentTarget;
		const formData = new FormData(form);
		const entries = formData.entries();
		const formPostObject = Object.fromEntries(entries);

		// Only save if version control is active
		if (sessionStorage.getItem("vcyc_active") === 'YES') {
			const formJson = JSON.stringify(formPostObject, null, 2);
			sessionStorage.setItem("formJson", formJson);
		}
		return true;
	});

	// Post Editor Sync to GitHub after page reload from session storage
	let formJson = sessionStorage.getItem("formJson");
	if (formJson && sessionStorage.getItem("vcyc_active") === 'YES') {
		let commitsPath = sessionStorage.getItem("vcyc_commits_path");
		if (commitsPath && commitsPath.length > 0) {
			let obj = JSON.parse(formJson);
			sync_to_github(obj.content);
		} else {
			console.warn("No commits path found in session storage");
		}
	}

	function sync_to_github(content) {
		let commits_path = sessionStorage.getItem("vcyc_commits_path");
		let postType = jq("#post_type").val().charAt(0).toUpperCase() + jq("#post_type").val().slice(1);
		let postId = jq("#post_ID").val();
		let message = `${vcyc.user}, ${postType}: ${postId}`;

		// Encode content to base64 using TextEncoder and ArrayBuffer -> Base64
		//let encoder = new TextEncoder();
		//let encodedContent = encoder.encode(content);
		//let base64Content = arrayBufferToBase64(encodedContent);
		//let base64Content = btoa(unescape(encodeURIComponent(content)));

		github_update(commits_path, content, message);
	}

	// Helper function to convert ArrayBuffer to Base64
	function arrayBufferToBase64(buffer) {
		let binary = '';
		let bytes = new Uint8Array(buffer);
		let len = bytes.byteLength;
		for (let i = 0; i < len; i++) {
			binary += String.fromCharCode(bytes[i]);
		}
		return window.btoa(binary);
	}

	// For Block Editor
	if (wp && "data" in wp) {
		console.log("wp.data exists");

		let isSaving = false;
		wp.data.subscribe(() => {
			const isSavingPost = wp.data.select('core/editor').isSavingPost();
			const isAutosavingPost = wp.data.select('core/editor').isAutosavingPost();
			const saveSuccess = wp.data.select('core/editor').didPostSaveRequestSucceed();

			if (isSavingPost && !isSaving) {
				isSaving = true;
			}

			if (!isSavingPost && !isAutosavingPost && isSaving && saveSuccess) {
				const postData = wp.data.select('core/editor').getCurrentPost();
				// Sync to Github
				sync_to_github(postData.content);

				// Reset the flag
				isSaving = false;
			}
		});
	} else {
		// Classic Editor use case being handled seperately
	}
}); 
