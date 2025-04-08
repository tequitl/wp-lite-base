    
export default function Focus(jq, forms) {
  jq("body").on("focus", "textarea.github-pat", function() {
     jq(".pat-error").remove();
  });

} //end of Focus function