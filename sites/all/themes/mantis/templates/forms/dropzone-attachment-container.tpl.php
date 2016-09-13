<div class="col-sm-6">
  <div class="ticket__section__attachments" id="<?php print $container['#name']; ?>">
    <h4>Click Upload or Drag &amp; Drop File</h4>
    <div class="fileUpload">
      <span>Upload</span>
    </div>
    <p>Attachment restrictions: maximum of 10 files per ticket and 500Mb per file</p>
  </div>
</div>
<div class="col-sm-6">
  <div class="ticket__section__progressbar" id="<?php print $container['#name']; ?>-preview">
    <div class="wrap-progress">
      <div class="file-name">
        <span><span data-dz-name></span><span class="file-size">(<span data-dz-size></span>)</span></span>
        <a href="#" title="" class="btn-close" data-dz-remove>×</a>
      </div>
      <div class="progress-container">
        <div id="myBar" class="progressbar" style="width:0%" data-dz-uploadprogress></div>
      </div>
      <div class="error-message"><span data-dz-errormessage></span></div>
    </div>
  </div>
</div>