<!-- detail_sparepart_auxiliaries_edit -->
<div id="detail-sparepart-auxiliaries-edit" class="modal fade" tabindex="-1" aria-labelledby="edit_poLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="edit_poLabel">
					Edit - Request Sparepart & Auxiliaries
				</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="" class="form-material m-t-40" enctype="multipart/form-data" id="form_detail_sparepart_auxiliaries_edit">
            @method('PUT')
			@csrf
            <div class="modal-body">
                <input type="hidden" class="form-control" id="request_number_pr" name="request_number">
                <div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Sparepart & Auxiliaries</label>
                    <select class="form-select" name="id_master_tool_auxiliaries" id="id_master_tool_auxiliaries_pr">
                            <option>Pilih Product</option>
                    </select>
                    @error('master_tool_auxiliaries')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
				<div class="mb-3 required-field">
                    <label for="example-text-input" class="form-label">Qty</label>
                    <input type="number" class="form-control" name="qty" id="qty_pr">
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
				<div class="mb-3">
                    <label for="example-text-input" class="form-label">Remarks</label>
                    <input type="text" class="form-control" name="remarks" id="remarks_pr">
                    @error('own_remarks')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning waves-effect" data-bs-dismiss="modal">
					<i class="bx bx-x-circle" title="Cancel" ></i> CANCEL
				</button>
                <button type="submit" class="btn btn-success waves-effect waves-light">
					<i class="bx bx-save" title="Back"></i> UPDATE
				</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->