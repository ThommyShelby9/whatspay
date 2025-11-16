<!-- latest jquery-->
<script src="/design/admin/assets/js/jquery.min.js"></script>
<!-- Bootstrap js-->
<script src="/design/admin/assets/js/bootstrap/bootstrap.bundle.min.js"></script>
<!-- feather icon js-->
<script src="{{ asset('design/admin/assets/js/icons/feather-icon/feather.min.js') }}"></script>
<script src="/design/admin/assets/js/icons/feather-icon/feather-icon.js"></script>
<!-- scrollbar js-->
@if ($viewData['user'] !== '')
    <input type="hidden" name="session" id="session" value="{{ $viewData['userid'] }}">
    <input type="hidden" name="baseUrl" id="baseUrl" value="{{ $viewData['baseUrl'] }}">
    <script src="/design/admin/assets/js/scrollbar/simplebar.js"></script>
    <script src="/design/admin/assets/js/scrollbar/custom.js"></script>
@endif
<!-- Sidebar jquery-->
<script src="/design/admin/assets/js/config.js"></script>
<!-- Plugins JS start-->
@if ($viewData['user'] !== '')
    <script src="/design/admin/assets/js/sidebar-menu.js"></script>
    <script src="/design/admin/assets/js/sidebar-pin.js"></script>
    <script src="/design/admin/assets/js/slick/slick.min.js"></script>
    <script src="/design/admin/assets/js/slick/slick.js"></script>
    <script src="/design/admin/assets/js/header-slick.js"></script>
    <!-- script src="/design/admin/assets/js/form-wizard/form-wizard.js"></script -->
    <!-- script src="/design/admin/assets/js/form-wizard/image-upload.js"></script -->
    <script src="/design/admin/assets/js/height-equal.js"></script>
    <script src="/design/admin/assets/js/datepicker/date-picker/datepicker.js"></script>
    <script src="/design/admin/assets/js/datepicker/date-picker/datepicker.en.js"></script>
    <script src="/design/admin/assets/js/dropzone/dropzone.js"></script>
    <script src="/design/admin/assets/js/datatable/datatables/jquery.dataTables.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.buttons.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/jszip.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/buttons.colVis.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/pdfmake.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/vfs_fonts.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.autoFill.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.select.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/buttons.bootstrap4.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/buttons.html5.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/buttons.print.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.bootstrap4.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.responsive.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/responsive.bootstrap4.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.keyTable.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.colReorder.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.fixedHeader.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.rowReorder.min.js"></script>
    <script src="/design/admin/assets/js/datatable/datatable-extension/dataTables.scroller.min.js"></script>
@endif
<!-- Plugins JS Ends-->
<!-- Theme js-->
<!-- script src="/design/admin/assets/js/plugins.bundle.js"></script -->
<script src="/custom/notify.min.js"></script>
<script src="/design/admin/assets/js/script.js?version={{ $version }}"></script>
@if ($viewData['user'] !== '')
    <!-- script src="/design/admin/assets/js/theme-customizer/customizer.js"></script -->
@endif
<script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.min.js"></script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script src="/custom/admin.js?version={{ $version }}"></script>
<!-- Plugin used-->

{{-- Custom scripts from child views (loaded after all dependencies) --}}
@stack('scripts')

@include('admin.toast')
