<!-- Google font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;800;900&amp;display=swap"
    rel="stylesheet">
<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i,900&amp;display=swap"
    rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/font-awesome.css">
<!-- ico-font-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/icofont.css">
<!-- Themify icon-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/themify.css">
<!-- Flag icon-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/flag-icon.css">
<!-- Feather icon-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/feather-icon.css">
<!-- Plugins css start-->
@if ($viewData['user'] !== '')
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/slick.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/slick-theme.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/scrollbar.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/animate.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/date-picker.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/dropzone.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/datatables.css">
    <link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/datatable-extension.css">
@endif
<!-- Plugins css Ends-->
<!-- Bootstrap css-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/vendors/bootstrap.css">
<!-- App css-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/style.css">
<link id="color" rel="stylesheet" href="/design/admin/assets/css/color-1.css" media="screen">
<!-- Responsive css-->
<link rel="stylesheet" type="text/css" href="/design/admin/assets/css/responsive.css">

<!-- Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<link href="https://cdn.jsdelivr.net/npm/apexcharts@3.35.3/dist/apexcharts.css" rel="stylesheet">

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />


<style>
    .notifyjs-corner {
        z-index: 9999 !important;
        /* Ensure notifications are always on top */
    }

    :root {
        --primary: #3b82f6;
        --success: #10b981;
        --info: #06b6d4;
        --warning: #f59e0b;
        --danger: #ef4444;
        --light: #f3f4f6;
    }

    .avatar {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .avatar-text {
        font-weight: bold;
    }

    .media-preview {
        min-height: 250px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--light) 0%, #ffffff 100%);
    }

    .bg-pattern {
        background-color: #fff;
        background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23f8f9fa' fill-opacity='1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }

    .font-size-24 {
        font-size: 24px;
    }

    .font-size-16 {
        font-size: 16px;
    }

    .fw-medium {
        font-weight: 500;
    }

    .category-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 5px;
    }

    .category-tag {
        background-color: #f0f9ff;
        color: #0369a1;
        border: 1px solid #0ea5e9;
        border-radius: 50px;
        padding: 4px 12px;
        font-size: 0.8rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
        transition: all 0.3s ease;
    }

    .category-tag:hover {
        background-color: #e0f2fe;
        box-shadow: 0 2px 8px rgba(3, 105, 161, 0.2);
        transform: translateY(-2px);
    }

    /* Chart containers */
    #views-clicks-chart,
    #device-chart,
    #weekday-chart {
        background: white;
    }

    .apexcharts-tooltip {
        background: rgba(0, 0, 0, 0.8) !important;
        border-radius: 6px;
    }

    /* Stats cards in performance tab */
    .bg-primary-subtle {
        background-color: rgba(59, 130, 246, 0.1);
    }

    .bg-success-subtle {
        background-color: rgba(16, 185, 129, 0.1);
    }

    .bg-info-subtle {
        background-color: rgba(6, 182, 212, 0.1);
    }

    .bg-warning-subtle {
        background-color: rgba(245, 158, 11, 0.1);
    }

    .text-primary {
        color: var(--primary);
    }

    .text-success {
        color: var(--success);
    }

    .text-info {
        color: var(--info);
    }

    .text-warning {
        color: var(--warning);
    }

    /* Tabs styling */
    .nav-tabs .nav-link {
        color: #6b7280;
        border: none;
        border-bottom: 2px solid transparent;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-tabs .nav-link:hover {
        color: var(--primary);
        border-bottom-color: transparent;
    }

    .nav-tabs .nav-link.active {
        color: var(--primary);
        background-color: transparent;
        border-bottom-color: var(--primary);
    }

    /* Card headers */
    .card-header {
        background-color: #ffffff;
        border-bottom: 1px solid #e5e7eb;
    }

    .card {
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border: 1px solid #e5e7eb;
    }

    /* Badges */
    .badge {
        padding: 0.4rem 0.8rem;
        font-weight: 500;
        font-size: 0.75rem;
    }

    /* Responsive improvements */
    @media (max-width: 768px) {
        .row {
            margin-right: 0 !important;
            margin-left: 0 !important;
        }

        #views-clicks-chart,
        #device-chart,
        #weekday-chart {
            height: 250px !important;
        }

        .col-lg-6,
        .col-lg-7,
        .col-lg-5 {
            padding-right: 0.5rem;
            padding-left: 0.5rem;
        }
    }

    .category-selector {
        max-height: 200px;
        overflow-y: auto;
        background-color: #f8f9fa;
    }

    .form-check {
        padding: 8px 12px;
        border-radius: 4px;
        transition: background-color 0.2s;
    }

    .form-check:hover {
        background-color: #e9ecef;
    }

    .select2-container .select2-selection--single {
        height: 38px;
        padding: 5px;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
</style>
