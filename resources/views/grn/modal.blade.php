<!-- input Lot Number -->
<div id="myModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Input Lot Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_lot_number" class="form-material m-t-40" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Lot Number</label>
                    <input class="form-control" type="text" name="lot_number" id="generatedCode" readonly>
                    <input class="form-control" type="hidden" name="id" id="idOke">
                    <input class="form-control" type="hidden" name="id_grn" id="id_good_receipt_notes">
                    @error('lot_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Qty</label>
                    <input class="form-control" type="number" name="qty_generate_barcode" id="" >
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <!-- <button type="submit" class="btn btn-primary waves-effect waves-light">Save & Add More</button> -->
                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<!-- input Lot Number -->
<div id="input_lot_edit" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Input Lot Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_lot_number" class="form-material m-t-40" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Lot Number</label>
                    <input class="form-control" type="text" name="lot_number" id="lot_numberok" readonly>
                    <input class="form-control" type="hidden" name="id" id="idOke2">
                    <input class="form-control" type="hidden" name="id_grn" id="id_good_receipt_notes_edit">
                    @error('lot_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Qty</label>
                    <input class="form-control" type="number" name="qty_generate_barcode" id="" >
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <!-- <button type="submit" class="btn btn-primary waves-effect waves-light">Save & Add More</button> -->
                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- Edit GRN PR detail -->
<div id="edit-pr-detail" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Edit GRN PR detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/simpan_po" class="form-material m-t-40" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Pilih Produk</label>
                    <select class="form-select" name="id_master_products">
                        <option>Pilih Produk</option>
                        
                    </select>
                    @error('external_no_lot')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Receipt Qty</label>
                    <input class="form-control" type="text" name="qty" id="generatedCode" readonly>
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Outstanding Qty</label>
                    <input class="form-control" type="text" name="qty" id="generatedCode" readonly>
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Units</label>
                    <select class="form-select" name="master_units_id" id="unit_code">
                        <option>Pilih Unit</option>
                      
                    </select>
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Note</label>
                    <textarea name="note" rows="4" cols="50" class="form-control"></textarea>
                                      
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <!-- <button type="submit" class="btn btn-primary waves-effect waves-light">Save & Add More</button> -->
                <button type="submit" class="btn btn-primary waves-effect waves-light">Update</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<!-- input Lot Number -->
<div id="external_lot" class="modal fade" tabindex="-1" aria-labelledby="external_lotLabel" aria-hidden="true" data-bs-scroll="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="external_lotLabel">Input External Lot Number</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" action="/update_ext_lot_number" class="form-material m-t-40" enctype="multipart/form-data">
            @method('PUT')
            @csrf
            <div class="modal-body">
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">External Lot Number</label>
                    <input class="form-control" type="text" name="ext_lot_number" id="" >
                    <input class="form-control" type="hidden" name="id" id="id_ext_lot">
                    @error('lot_number')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="example-text-input" class="form-label">Qty</label>
                    <input class="form-control" type="number" name="qty" id="" >
                    @error('qty')
                        <div class="form-group has-danger mb-0">
                            <div class="form-control-feedback">{{ $message }}</div>
                        </div>
                    @enderror
                </div>
                

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">Back</button>
                <!-- <button type="submit" class="btn btn-primary waves-effect waves-light">Save & Add More</button> -->
                <button type="submit" class="btn btn-primary waves-effect waves-light">Save</button>
            </div>

            </form>
        </div><!-- /.modal-content -->
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->