<link href="{{ asset('assets/css/customselect2.css') }}" id="app-style" rel="stylesheet" type="text/css" />
<style>
    .custom-margin {
        margin-top: -16px;
    }
    input::placeholder {
        color: lightgray !important;
    }
    input:focus::placeholder {
        color: lightgray; /* Optional: Change color on focus */
    }
    .modal-header {
        background-color: #5156be;
        color: white;
        border-bottom: none;
    }
    .modal-header .modal-title {
        color: inherit;
    }
    .modal-header button.btn-close {
        background-color: #ffffff;
    }
    div.field-wrapper label {
        text-align: right;
        padding-right: 50px
    }

    div.required-field label::after {
        content: " *";
        color: red;
    }
    .custom-bg-gray {
        background-color: #e2e2e2;
    }
</style>