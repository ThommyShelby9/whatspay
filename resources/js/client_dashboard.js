// Budget Overview Chart initialization
function initBudgetChart(elementId, data) {
  const budgetChart = new ApexCharts(document.getElementById(elementId), {
    series: [{
      name: 'Budget',
      data: [
        data.paidBudget,
        data.pendingBudget,
        data.remainingBudget
      ]
    }],
    chart: {
      type: 'bar',
      height: 320,
      toolbar: {
        show: false
      }
    },
    plotOptions: {
      bar: {
        horizontal: false,
        columnWidth: '45%',
        endingShape: 'rounded'
      },
    },
    dataLabels: {
      enabled: false
    },
    colors: ['#28a745', '#ffc107', '#007bff'],
    xaxis: {
      categories: ['Dépensé', 'En cours', 'Restant'],
    },
    tooltip: {
      y: {
        formatter: function (val) {
          return val.toLocaleString() + " F";
        }
      }
    }
  });
  
  budgetChart.render();
}

// Campaign Status Chart initialization
function initCampaignStatusChart(elementId, data) {
  const campaignChart = new ApexCharts(document.getElementById(elementId), {
    series: [
      data.pending,
      data.accepted,
      data.rejected
    ],
    chart: {
      type: 'donut',
      height: 320
    },
    labels: ['En attente', 'Acceptées', 'Rejetées'],
    colors: ['#007bff', '#28a745', '#dc3545'],
    responsive: [{
      breakpoint: 480,
      options: {
        chart: {
          width: 200
        },
        legend: {
          position: 'bottom'
        }
      }
    }],
    tooltip: {
      y: {
        formatter: function(val) {
          return val + " campagnes";
        }
      }
    }
  });
  
  campaignChart.render();
}

// Load assignments for a task
function loadTaskAssignments(taskId, modalId) {
  const modal = document.getElementById(modalId);
  const loadingDiv = modal.querySelector('.loading');
  const contentDiv = modal.querySelector('.content');
  
  loadingDiv.classList.remove('d-none');
  contentDiv.classList.add('d-none');
  
  // AJAX call to get assignments data
  fetch(`/admin/task/${taskId}/assignments`)
    .then(response => response.json())
    .then(data => {
      loadingDiv.classList.add('d-none');
      contentDiv.classList.remove('d-none');
      
      if (data.success) {
        // Render assignments table
        renderAssignmentsTable(contentDiv, data);
      } else {
        contentDiv.innerHTML = '<div class="alert alert-danger">Impossible de charger les données</div>';
      }
    })
    .catch(error => {
      loadingDiv.classList.add('d-none');
      contentDiv.classList.remove('d-none');
      contentDiv.innerHTML = '<div class="alert alert-danger">Une erreur est survenue lors du chargement des données</div>';
    });
}

// Render assignments table
function renderAssignmentsTable(container, data) {
  let html = `
    <h6 class="mb-3">Campagne: ${data.task.name}</h6>
    <div class="table-responsive">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Diffuseur</th>
            <th>Vues</th>
            <th>Statut</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
  `;
  
  if (data.assignments.length > 0) {
    data.assignments.forEach(function(assignment) {
      let statusBadge = '';
      if (assignment.status === 'PENDING') {
        statusBadge = '<span class="badge bg-warning">En attente</span>';
      } else if (assignment.status === 'ACCEPTED') {
        statusBadge = '<span class="badge bg-primary">Acceptée</span>';
      } else if (assignment.status === 'COMPLETED') {
        statusBadge = '<span class="badge bg-success">Terminée</span>';
      } else if (assignment.status === 'REJECTED') {
        statusBadge = '<span class="badge bg-danger">Rejetée</span>';
      }
      
      html += `
        <tr>
          <td>${assignment.agent_name}</td>
          <td>${assignment.views ?? 'N/A'}</td>
          <td>${statusBadge}</td>
          <td>${new Date(assignment.created_at).toLocaleDateString()}</td>
        </tr>
      `;
    });
  } else {
    html += `
      <tr>
        <td colspan="4" class="text-center">Aucun diffuseur assigné à cette campagne</td>
      </tr>
    `;
  }
  
  html += `
        </tbody>
      </table>
    </div>
  `;
  
  container.innerHTML = html;
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
  // Initialize DataTable
  if ($.fn.DataTable && document.getElementById('client-tasks-table')) {
    $('#client-tasks-table').DataTable({
      language: {
        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/fr-FR.json'
      },
      order: [[0, 'desc']],
      pageLength: 10
    });
  }
  
  // Initialize charts if elements exist
  if (window.ApexCharts) {
    if (document.getElementById('budget-overview-chart')) {
      initBudgetChart('budget-overview-chart', window.budgetData);
    }
    
    if (document.getElementById('campaign-status-chart')) {
      initCampaignStatusChart('campaign-status-chart', window.campaignData);
    }
  }
  
  // Assignments modal initialization
  const assignmentsModal = document.getElementById('assignmentsModal');
  if (assignmentsModal) {
    assignmentsModal.addEventListener('show.bs.modal', function(event) {
      const button = event.relatedTarget;
      const taskId = button.getAttribute('data-task-id');
      loadTaskAssignments(taskId, 'assignmentsModal');
    });
  }
  
  // Export functions
  const exportButtons = document.querySelectorAll('#exportCSV, #exportExcel, #exportPDF');
  exportButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      alert('Fonctionnalité d\'export à implémenter');
    });
  });
});