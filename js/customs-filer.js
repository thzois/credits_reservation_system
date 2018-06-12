
$(document).ready(function() {
	
	//Example 1
	$('.filer_input').filer({
		showThumbs: true,
		changeInput: '<div class="jFiler-custom-wrapper"><a class="jFiler-btn-form">Browse Files</a></div>',
		captions: {
					button: "Choose Photo",
			},
			templates: {
					box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
					item: '<li class="jFiler-item">\
											<div class="jFiler-item-container">\
													<div class="jFiler-item-inner">\
															<div class="jFiler-item-thumb">\
																	<div class="jFiler-item-status"></div>\
																	<div class="jFiler-item-info">\
																			<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 23}}</b></span>\
																			<span class="jFiler-item-others">{{fi-size2}}</span>\
																	</div>\
																	{{fi-image}}\
															</div>\
															<div class="jFiler-item-assets jFiler-row">\
																	<ul class="list-inline pull-left">\
																			<li>{{fi-progressBar}}</li>\
																	</ul>\
																	<ul class="list-inline pull-right">\
																			<li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
																	</ul>\
															</div>\
													</div>\
											</div>\
									</li>',
					itemAppend: '<li class="jFiler-item">\
													<div class="jFiler-item-container">\
															<div class="jFiler-item-inner">\
																	<div class="jFiler-item-thumb">\
																			<div class="jFiler-item-status"></div>\
																			<div class="jFiler-item-info">\
																					<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 23}}</b></span>\
																					<span class="jFiler-item-others">{{fi-size2}}</span>\
																			</div>\
																			{{fi-image}}\
																	</div>\
																	<div class="jFiler-item-assets jFiler-row">\
																			<ul class="list-inline pull-left">\
																					<li><span class="jFiler-item-others">{{fi-icon}}</span></li>\
																			</ul>\
																			<ul class="list-inline pull-right">\
																					<li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
																			</ul>\
																	</div>\
															</div>\
													</div>\
											</li>',
					progressBar: '<div class="bar"></div>',
					itemAppendToEnd: false,
					removeConfirmation: true,
					_selectors: {
							list: '.jFiler-items-list',
							item: '.jFiler-item',
							progressBar: '.bar',
							remove: '.jFiler-item-trash-action'
					}
			},
			
	});
	
	//Example 2
	$("#filer_input2").filer({
			limit: 15,
			maxSize: null,
			extensions: null,
			changeInput: '<div class="jFiler-custom-wrapper"><a class="jFiler-btn-form">Browse Files (up to 15 photos)</a></div>',
			showThumbs: true,
			theme: "dragdropbox",
			templates: {
					box: '<ul class="jFiler-items-list jFiler-items-grid"></ul>',
					item: '<li class="jFiler-item">\
											<div class="jFiler-item-container">\
													<div class="jFiler-item-inner">\
															<div class="jFiler-item-thumb">\
																	<div class="jFiler-item-status"></div>\
																	<div class="jFiler-item-info">\
																			<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 15}}</b></span>\
																			<span class="jFiler-item-others">{{fi-size2}}</span>\
																	</div>\
																	{{fi-image}}\
															</div>\
															<div class="jFiler-item-assets jFiler-row">\
																	<ul class="list-inline pull-left">\
																			<li>{{fi-progressBar}}</li>\
																	</ul>\
																	<ul class="list-inline pull-right">\
																			<li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
																	</ul>\
															</div>\
													</div>\
											</div>\
									</li>',
					itemAppend: '<li class="jFiler-item">\
													<div class="jFiler-item-container">\
															<div class="jFiler-item-inner">\
																	<div class="jFiler-item-thumb">\
																			<div class="jFiler-item-status"></div>\
																			<div class="jFiler-item-info">\
																					<span class="jFiler-item-title"><b title="{{fi-name}}">{{fi-name | limitTo: 15}}</b></span>\
																					<span class="jFiler-item-others">{{fi-size2}}</span>\
																			</div>\
																			{{fi-image}}\
																	</div>\
																	<div class="jFiler-item-assets jFiler-row">\
																			<ul class="list-inline pull-left">\
																					<li><span class="jFiler-item-others">{{fi-icon}}</span></li>\
																			</ul>\
																			<ul class="list-inline pull-right">\
																					<li><a class="icon-jfi-trash jFiler-item-trash-action"></a></li>\
																			</ul>\
																	</div>\
															</div>\
													</div>\
											</li>',
					progressBar: '<div class="bar"></div>',
					itemAppendToEnd: false,
					removeConfirmation: true,
					_selectors: {
							list: '.jFiler-items-list',
							item: '.jFiler-item',
							progressBar: '.bar',
							remove: '.jFiler-item-trash-action'
					}
			},
			
			dragDrop: {
					dragEnter: null,
					dragLeave: null,
					drop: null,
			},
			uploadFile: {
					url: "./php/upload.php",
					data: null,
					type: 'POST',
					enctype: 'multipart/form-data',
					beforeSend: function(){},
					success: function(data, el){
							var parent = el.find(".jFiler-jProgressBar").parent();
							el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
									$("<div class=\"jFiler-item-others text-success\"><i class=\"icon-jfi-check-circle\"></i> Success</div>").hide().appendTo(parent).fadeIn("slow");    
							});
					},
					error: function(el){
							var parent = el.find(".jFiler-jProgressBar").parent();
							el.find(".jFiler-jProgressBar").fadeOut("slow", function(){
									$("<div class=\"jFiler-item-others text-error\"><i class=\"icon-jfi-minus-circle\"></i> Error</div>").hide().appendTo(parent).fadeIn("slow");    
							});
					},
					statusCode: null,
					onProgress: null,
					onComplete: null
			},
			files: null,
			addMore: false,
			clipBoardPaste: true,
			excludeName: null,
			beforeRender: null,
			afterRender: null,
			beforeShow: null,
			beforeSelect: null,
			onSelect: null,
			afterShow: null,
			onRemove: function(itemEl, file, id, listEl, boxEl, newInputEl, inputEl){
					var file = file.name;
					$.post('./php/remove_file.php', {file: file});
			},
			onEmpty: null,
			options: null,
			captions: {
					button: "Choose Files",
					feedback: "Choose files To Upload",
					feedback2: "files were chosen",
					drop: "Drop file here to Upload",
					removeConfirmation: "Are you sure you want to remove this file?",
					errors: {
							filesLimit: "Only {{fi-limit}} files are allowed to be uploaded.",
							filesType: "Only Images are allowed to be uploaded.",
							filesSize: "{{fi-name}} is too large! Please upload file up to {{fi-maxSize}} MB.",
							filesSizeAll: "Files you've choosed are too large! Please upload files up to {{fi-maxSize}} MB."
					}
			}
	});
	
});