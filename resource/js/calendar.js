$(document).ready(function() {
	var getData = $("#getData").text();
	var siteUrl = $("#siteUrl").text();
	var getResaURL = siteUrl + "/resa/get";
	var getCostURL = siteUrl + "/cost/get";
	var createURL = siteUrl + "/resa/create";
	var deleteURL = siteUrl + "/resa/delete";
	var $defer = 
		$.ajax({
			url : getResaURL,
			type : "GET",
			data : getData,
			dataType : "html",
			cache : false,
			success : function(result) {
			$("#calendarContent").append(result);
			}
	});
	$defer.success( 
			$.ajax({
				url : getCostURL,
				type : "GET",
				data : getData,
				dataType : "html",
				success : function(result) {
//					alert("success load"+result);
					var cost = jQuery.parseJSON(result);
					for (var key in cost) {
						if (key=="sum") {
							$(".debt").html(cost.debtPrev);
							$(".totalDepassementPrev").html(cost.sum.depassementPrev);
							$(".cost").html(cost.sum.cost);
							$(".total").html(cost.sum.total);
							$(".totalDepassement").html(cost.sum.depassement);
						} else if (key=="children") {
							for (var childId in cost.children) {
								var child=cost.children[childId]
								if ("&nbsp;"==child.resa.str) {
									$("."+childId+"-cost").html("0");
								} else {
									$("."+childId+"-cost").html(child.resa.str+ " = " + child.resa.total);
								}
								if ("&nbsp;"==child.depassementPrev.str) {
									$("."+childId+"-costDepassementPrev").html("0");
								} else {
									$("."+childId+"-costDepassementPrev").html(child.depassementPrev.str+ " = " + child.depassementPrev.total);
								}
								if ("&nbsp;"==child.depassement.str) {
									$("."+childId+"-costDepassement").html("0");
								} else {
									$("."+childId+"-costDepassement").html(child.depassement.str+ " = " + child.depassement.total);
								}
								$("."+childId+"-total").html(child.total);
							}
						}
					}
				}
			})
	);
	$(".period").live("click", function() {
		var text = $(this).text();
		text = $.trim(text);
		var data = text.split("-");
//alert("click "+text);
		var $defer = 
			$.ajax({
				url : createURL,
				type : "POST",
				cache : false,
				data : {
					year : data[0],
					month : data[1],
					day : data[2],
					period : data[3],
					child : data[4]
				},
				success : function(result) {
//alert("success period"+result);
					var resas = jQuery.parseJSON(result);
					$.each(resas, function(property, value) {
						var dateStr=value.date.split(" ");
						dateStr=dateStr[0].split("-");
				        var cellStr = dateStr[0]+"-"+dateStr[1]+"-"+dateStr[2].replace(/^0+/, '')+"-"+value.period_id+"-"+value.child_id+"-";
				        var cell = $("p.content:contains("+cellStr+")").parent();
				        var type = value.resa_type;
				        if (type==3) {
							cell.attr("class", "period_3");
				        } else {
							cell.attr("class", "period_1");
				        }
				    });
					
					$.ajax({
						url : getCostURL,
						type : "GET",
						cache : false,
						data : getData,
						dataType : "html",
						success : function(result) {
							var cost = jQuery.parseJSON(result);
							for (var key in cost) {
								if (key=="sum") {
									$(".debt").html(cost.debtPrev);
									$(".totalDepassementPrev").html(cost.sum.depassementPrev);
									$(".cost").html(cost.sum.cost);
									$(".total").html(cost.sum.total);
									$(".totalDepassement").html(cost.sum.depassement);
								} else if (key=="children") {
									for (var childId in cost.children) {
										var child=cost.children[childId]
										if ("&nbsp;"==child.resa.str) {
											$("."+childId+"-cost").html("0");
										} else {
											$("."+childId+"-cost").html(child.resa.str+ " = " + child.resa.total);
										}
										if ("&nbsp;"==child.depassementPrev.str) {
											$("."+childId+"-costDepassementPrev").html("0");
										} else {
											$("."+childId+"-costDepassementPrev").html(child.depassementPrev.str+ " = " + child.depassementPrev.total);
										}
										if ("&nbsp;"==child.depassement.str) {
											$("."+childId+"-costDepassement").html("0");
										} else {
											$("."+childId+"-costDepassement").html(child.depassement.str+ " = " + child.depassement.total);
										}
										$("."+childId+"-total").html(child.total);
									}
								}
							}
						}
					})
				},
				error : function(result) {
					str = JSON.stringify(result, null, 4); // (Optional) beautiful indented output.
					alert("failure period "+str); // Displays output using window.alert()
				}
			});
	});

	$(".period_1").live("click", function() {
		var text = $(this).text();
		text = $.trim(text);
		var data = text.split("-");
		var aObj = $(this);
		var $defer = 
			$.ajax({
				url : deleteURL,
				type : "POST",
				cache : false,
				data : {
					year : data[0],
					month : data[1],
					day : data[2],
					period : data[3],
					child : data[4]
				},
				success : function(result) {
//alert("success period1 "+result);
					aObj.attr("class", "period");
					$.ajax({
						url : getCostURL,
						type : "GET",
						cache : false,
						data : getData,
						dataType : "html",
						success : function(result) {
							var cost = jQuery.parseJSON(result);
							for (var key in cost) {
								if (key=="sum") {
									$(".debt").html(cost.debtPrev);
									$(".totalDepassementPrev").html(cost.sum.depassementPrev);
									$(".cost").html(cost.sum.cost);
									$(".total").html(cost.sum.total);
									$(".totalDepassement").html(cost.sum.depassement);
								} else if (key=="children") {
									for (var childId in cost.children) {
										var child=cost.children[childId]
										if ("&nbsp;"==child.resa.str) {
											$("."+childId+"-cost").html("0");
										} else {
											$("."+childId+"-cost").html(child.resa.str+ " = " + child.resa.total);
										}
										if ("&nbsp;"==child.depassementPrev.str) {
											$("."+childId+"-costDepassementPrev").html("0");
										} else {
											$("."+childId+"-costDepassementPrev").html(child.depassementPrev.str+ " = " + child.depassementPrev.total);
										}
										if ("&nbsp;"==child.depassement.str) {
											$("."+childId+"-costDepassement").html("0");
										} else {
											$("."+childId+"-costDepassement").html(child.depassement.str+ " = " + child.depassement.total);
										}
										$("."+childId+"-total").html(child.total);
									}
								}
							}
						}
					})
				},
				error : function(result) {
//					alert("failure period1 "+result);
				}
			});
	});

	$(".period_3").live("click", function() {
		var text = $(this).text();
		text = $.trim(text);
		var data = text.split("-");
		var aObj = $(this);
//alert("period3 "+aObj);
		var $defer = 
			$.ajax({
				url : deleteURL,
				type : "POST",
				cache : false,
				data : {
					year : data[0],
					month : data[1],
					day : data[2],
					period : data[3],
					child : data[4]
				},
				success : function(result) {
//alert("success period3 "+result);
					aObj.attr("class", "period");
					$.ajax({
						url : getCostURL,
						type : "GET",
						data : getData,
						cache : false,
						dataType : "html",
						success : function(result) {
							var cost = jQuery.parseJSON(result);
							for (var key in cost) {
								if (key=="sum") {
									$(".debt").html(cost.debtPrev);
									$(".totalDepassementPrev").html(cost.sum.depassementPrev);
									$(".cost").html(cost.sum.cost);
									$(".total").html(cost.sum.total);
									$(".totalDepassement").html(cost.sum.depassement);
								} else if (key=="children") {
									for (var childId in cost.children) {
										var child=cost.children[childId]
										if ("&nbsp;"==child.resa.str) {
											$("."+childId+"-cost").html("0");
										} else {
											$("."+childId+"-cost").html(child.resa.str+ " = " + child.resa.total);
										}
										if ("&nbsp;"==child.depassementPrev.str) {
											$("."+childId+"-costDepassementPrev").html("0");
										} else {
											$("."+childId+"-costDepassementPrev").html(child.depassementPrev.str+ " = " + child.depassementPrev.total);
										}
										if ("&nbsp;"==child.depassement.str) {
											$("."+childId+"-costDepassement").html("0");
										} else {
											$("."+childId+"-costDepassement").html(child.depassement.str+ " = " + child.depassement.total);
										}
										$("."+childId+"-total").html(child.total);
									}
								}
							}
						}
					})
				},
				error : function(result) {
//					alert("failure period3 "+result);
				}
			});

	});

});
