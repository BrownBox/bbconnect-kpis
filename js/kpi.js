jQuery(document).ready(function () {
	// Apply filters on change
	jQuery('#summary-segments, #summary-months, #summary-rules, #summary-display').change(function() {
		jQuery('#summary-months').parent('form').trigger('submit');
	});

	// Generate XLS Reports
	jQuery("#report-download").click(function(){
	        tableExport = jQuery('#table-donor-report').tableExport({
	            formats: ['csv'],
	            filename: 'Donor Report',
	            exportButtons: false
	        });
	        exportData = tableExport.getExportData()['table-donor-report']['csv'];
	        tableExport.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
	});

	jQuery("#segment-report-download").click(function(){
	        tableExport = jQuery('#table-segment-report').tableExport({
	            formats: ['csv'],
	            filename: 'Segment Report',
	            exportButtons: false
	        });
	        exportData = tableExport.getExportData()['table-segment-report']['csv'];
	        tableExport.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
	});

	jQuery("#report-download-campaign").click(function(){
	        tableExport = jQuery('#campaign_report_table').tableExport({
	            formats: ['csv'],
	            filename: 'Campaign Report',
	            exportButtons: false
	        });
	        exportData = tableExport.getExportData()['campaign_report_table']['csv'];
	        tableExport.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
	});

	jQuery("#channel-report-download").click(function(){
	        tableExport = jQuery('#table-channel-report').tableExport({
	            formats: ['csv'],
	            filename: 'Channel Report',
	            exportButtons: false
	        });
	        exportData = tableExport.getExportData()['table-channel-report']['csv'];
	        tableExport.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
	});
	
	jQuery("#donation-list-download").click(function(){
	        tableExport = jQuery('#donation_list_report').tableExport({
	            formats: ['csv'],
	            filename: 'Donation List',
	            exportButtons: false
	        });
	        exportData = tableExport.getExportData()['donation_list_report']['csv'];
	        tableExport.export2file(exportData.data, exportData.mimeType, exportData.filename, exportData.fileExtension);
	});
});