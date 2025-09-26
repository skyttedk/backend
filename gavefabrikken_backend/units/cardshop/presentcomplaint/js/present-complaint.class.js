import Base from '../../main/js/base.js';
import tpPresentComplaint from '../tp/present-complaint.tp.js?v=123';
import Complaint from '../../../valgshop/main/js/complaint.class.js';

export default class PresentComplaint extends Base {
    constructor(LANGUAGE) {
        super();
        this.LANGUAGE = LANGUAGE;
        this.timer;
        this.complaints = [];
        this.filteredComplaints = [];
        this.currentView = 'all'; // 'all', 'search'
        
        this.init();
    }

    async init() {
        this.Layout(".cardshop-sidebar", {});
        this.SetEvents();
        await this.LoadAllComplaints();
    }

    Layout(targetClass, data) {
        $(targetClass).html(tpPresentComplaint.searchform());
        $(targetClass).append(tpPresentComplaint.complaintsList());
        $(targetClass).append(tpPresentComplaint.loadingIndicator());
    }

    SetEvents() {
        let self = this;

        // Search functionality
        $(".cardshop #complaint-search").unbind("keyup").keyup(() => {
            clearTimeout(this.timer);
            this.timer = setTimeout(() => this.Search(), 500);
        });

        // Clear search
        $("#clear-search").unbind("click").click(() => {
            $("#complaint-search").val("");
            this.ShowAllComplaints();
        });

        // Export CSV functionality
        $("#export-csv").unbind("click").click(() => {
            this.ExportCsv();
        });

        // Refresh data
        $("#refresh-data").unbind("click").click(() => {
            this.LoadAllComplaints();
        });

        // View filters
        $(".view-filter").unbind("click").click(function() {
            $(".view-filter").removeClass("active");
            $(this).addClass("active");
            let filter = $(this).data("filter");
            self.FilterView(filter);
        });
    }

    async LoadAllComplaints() {
        this.ShowLoadingIndicator();
        
        try {
            let result = await super.post("cardshop/presentcomplaint/getAllComplaints");
            
            if (result && result.status === 1) {
                this.complaints = result.result;
                this.ShowAllComplaints();
                this.UpdateStatusIndicator(this.complaints.length + " reklamationer indlæst");
            } else {
                this.ShowError("Kunne ikke indlæse reklamationer");
            }
        } catch (error) {
            console.error("Error loading complaints:", error);
            this.ShowError("Fejl ved indlæsning af reklamationer");
        } finally {
            this.HideLoadingIndicator();
        }
    }

    async Search() {
        let searchText = $("#complaint-search").val().trim();
        
        if (searchText === "") {
            this.ShowAllComplaints();
            return;
        }

        if (searchText.length < 3) {
            this.ShowMessage("Søgning kræver mindst 3 tegn");
            return;
        }

        this.ShowLoadingIndicator();

        try {
            let formData = { 
                text: searchText, 
                LANGUAGE: this.LANGUAGE 
            };

            let result = await super.post("cardshop/presentcomplaint/search", formData);
            
            if (result && result.status === 1) {
                this.filteredComplaints = result.result;
                this.BuildComplaintsList(this.filteredComplaints);
                this.currentView = 'search';
                this.UpdateStatusIndicator(this.filteredComplaints.length + " reklamationer fundet");
            } else {
                this.ShowError("Søgning mislykkedes");
            }
        } catch (error) {
            console.error("Search error:", error);
            this.ShowError("Der opstod en søgefejl");
        } finally {
            this.HideLoadingIndicator();
        }
    }

    ShowAllComplaints() {
        this.BuildComplaintsList(this.complaints);
        this.currentView = 'all';
        this.UpdateStatusIndicator(this.complaints.length + " reklamationer i alt");
    }

    BuildComplaintsList(complaintsData) {
        $("#complaints-list").html("");

        if (!complaintsData || complaintsData.length === 0) {
            $("#complaints-list").html(tpPresentComplaint.noResults());
            return;
        }

        // Group complaints by shop for better organization
        let groupedByShop = this.GroupComplaintsByShop(complaintsData);

        let html = "";
        for (let shopName in groupedByShop) {
            let shopComplaints = groupedByShop[shopName];
            html += tpPresentComplaint.shopGroup(shopName, shopComplaints.length);
            
            shopComplaints.forEach(complaint => {
                html += tpPresentComplaint.complaintItem(complaint);
            });
        }

        $("#complaints-list").html(html);
        this.SetComplaintEvents();
    }

    GroupComplaintsByShop(complaintsData) {
        let grouped = {};
        
        complaintsData.forEach(complaint => {
            let shopName = complaint.shop_name || "Ukendt Butik";
            if (!grouped[shopName]) {
                grouped[shopName] = [];
            }
            grouped[shopName].push(complaint);
        });

        // Sort shops alphabetically
        let sortedGrouped = {};
        Object.keys(grouped).sort().forEach(key => {
            sortedGrouped[key] = grouped[key];
        });

        return sortedGrouped;
    }

    SetComplaintEvents() {
        let self = this;

        // Click on complaint item to view details
        $(".complaint-item").unbind("click").click(function() {
            let userId = $(this).data("user-id");
            let shopId = $(this).data("shop-id");
            self.ViewComplaintDetail(userId, shopId);
        });

        // No edit functionality needed - removed per user request
    }

    async ViewComplaintDetail(userId, shopId) {
        let self = this;
        try {
            let result = await super.post("cardshop/presentcomplaint/getComplaintDetail/" + userId);
            
            if (result && result.status === 1 && result.result.length > 0) {
                let complaintData = result.result[0];
                let modalHtml = tpPresentComplaint.complaintDetailModal(complaintData);
                
                super.OpenModal(
                    "Reklamationsdetaljer - " + complaintData.company_name,
                    modalHtml,
                    tpPresentComplaint.modalCloseButton()
                );
                
                // Set up close button event after modal is opened
                setTimeout(() => {
                    $("#complaint-modal-close").unbind("click").click(function() {
                        self.CloseModal();
                    });
                }, 100);
                
            } else {
                super.Toast("Ingen reklamationsdetaljer fundet", "Fejl", true);
            }
        } catch (error) {
            console.error("Error loading complaint detail:", error);
            super.Toast("Fejl ved indlæsning af reklamationsdetaljer", "Fejl", true);
        }
    }

    CloseModal() {
        // Use Bootstrap's built-in modal hide to avoid conflicts
        $('.modal').modal('hide');
        
        // Only do manual cleanup if Bootstrap doesn't handle it properly
        setTimeout(() => {
            if ($('.modal-backdrop').length > 0) {
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
            }
        }, 300);
        
        // Also try the parent class method if available
        if (super.CloseModal) {
            super.CloseModal();
        }
    }

    FilterView(filter) {
        let dataToShow = this.currentView === 'search' ? this.filteredComplaints : this.complaints;
        let filteredData = dataToShow;

        switch (filter) {
            case 'recent':
                // Show complaints from last 7 days
                let weekAgo = new Date();
                weekAgo.setDate(weekAgo.getDate() - 7);
                filteredData = dataToShow.filter(complaint => {
                    let complaintDate = new Date(complaint.updated_date || complaint.created_date);
                    return complaintDate >= weekAgo;
                });
                break;
            case 'all':
            default:
                filteredData = dataToShow;
                break;
        }

        this.BuildComplaintsList(filteredData);
        this.UpdateStatusIndicator(filteredData.length + " reklamationer (" + filter + " visning)");
    }

    ExportCsv() {
        // Open export URL in new window to trigger download
        window.open(BASEURL + "cardshop/presentcomplaint/exportCsv", "_blank");
        super.Toast("Eksport startet - filen vil blive downloadet om kort tid");
    }

    ShowLoadingIndicator() {
        $("#loading-indicator").show();
        $("#complaints-list").hide();
    }

    HideLoadingIndicator() {
        $("#loading-indicator").hide();
        $("#complaints-list").show();
    }

    ShowMessage(message) {
        $("#complaints-list").html(tpPresentComplaint.messageDisplay(message));
    }

    ShowError(message) {
        $("#complaints-list").html(tpPresentComplaint.errorDisplay(message));
        super.Toast(message, "Error", true);
    }

    UpdateStatusIndicator(message) {
        $("#status-indicator").text(message);
    }
}