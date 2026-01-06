import './bootstrap';
import './particles';

import Alpine from 'alpinejs';

// Import jQuery
import $ from 'jquery';
window.$ = window.jQuery = $;

// Import DataTables
import 'datatables.net';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import 'datatables.net-responsive';
import 'datatables.net-responsive-dt/css/responsive.dataTables.css';


// Import SweetAlert2
import Swal from 'sweetalert2';
window.Swal = Swal;

// Import custom DataTable helpers
import { initServerSideDataTable, confirmDelete } from './modules/datatable-helpers';
window.initServerSideDataTable = initServerSideDataTable;
window.confirmDelete = confirmDelete;

window.Alpine = Alpine;

Alpine.start();


