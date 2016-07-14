<?php

function modal ($id, $title, $body, $data) {
    $html = '<div id="'.$id.'" class="modal fade" style="display:none" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <div class="modal-header">
                        <button aria-hidden="true" class="close" data-dismiss="modal" type="button">&times;</button>
                        <h4 class="modal-title">' . $title .'</h4>
                      </div>
                      <div class="modal-body">' . $body .'</div>
                      <div class="modal-footer">
                          <a href="#" class="btn btn-default" data-dismiss="modal">Cancel</a>
                          <a href="' . $data['confirm_url'] .'" class="btn btn-primary" id="confirm">Confirm</a>
                      </div>
                    </div>
                  </div>
                </div>';
    echo $html;
};
