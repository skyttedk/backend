// Employee Management Module
class EmployeeManager {
    constructor(mailPortal) {
        this.mailPortal = mailPortal;
        this.selectedEmployees = [];
    }

    renderEmployeesList() {
        const tbody = $('#employeesTableBody');
        tbody.empty();
        
        this.mailPortal.dataManager.employees.forEach(employee => {
            const statusClass = employee.email_status === 'sent' ? 'status-sent' : 
                              employee.email_status === 'pending' ? 'status-pending' : 'status-error';
            
            const row = $(`
                <tr>
                    <td><input type="checkbox" class="employee-select" data-id="${employee.id}"></td>
                    <td>${employee.name}</td>
                    <td>${employee.email}</td>
                    <td>${employee.username}</td>
                    <td><span class="${statusClass}">${employee.email_status}</span></td>
                    <td>${employee.sent_date}</td>
                    <td>${employee.error_message}</td>
                    <td>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="mailPortal.employeeManager.sendEmail(${employee.id})" title="Send Email">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                            <button class="btn btn-outline-info" onclick="mailPortal.employeeManager.showEmailHistory(${employee.id})" title="Vis Historie">
                                <i class="fas fa-history"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `);
            tbody.append(row);
        });
    }

    updateSelectedEmployees() {
        const selected = $('.employee-select:checked').map(function() {
            return parseInt($(this).data('id'));
        }).get();
        
        this.selectedEmployees = selected;
        $('#bulkEmailBtn').prop('disabled', selected.length === 0);
    }

    sendEmail(employeeId) {
        console.log('Send email to employee:', employeeId);
        alert('Email sending functionality not implemented yet');
    }

    showEmailHistory(employeeId) {
        console.log('Show email history for employee:', employeeId);
        alert('Email history functionality not implemented yet');
    }

    showBulkEmailModal() {
        console.log('Show bulk email modal for employees:', this.selectedEmployees);
        alert('Bulk email functionality not implemented yet');
    }
}