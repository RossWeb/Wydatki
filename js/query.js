loadAjax(); // pierwsze zaladowanie danych

$(document).ready(function() {

	show();
	change();
	click();

});
function show() {
	showList("#expensesList", "ExpList");
	showList("#incomeList", "IncList");
	showFilter("#expensesListFilter", "#expensesButtonFilter");
	showFilter("#incomeListFilter", "#incomeButtonFilter");

}
function change() {
	$(document).on('change', '#expensesList', function() {
		showList("#expensesList", "ExpList");

	});

	$(document).on('change', '#incomeList', function() {
		showList("#incomeList", "IncList");

	});
	$(document).on('change', '#expensesListFilter', function() {
		showFilter("#expensesListFilter", "#expensesButtonFilter");
	});
	$(document).on('change', '#incomeListFilter', function() {
		showFilter("#incomeListFilter", "#incomeButtonFilter");

	});

	$(document).on('change', '#editCheckExpData', function() {
		var selected = $("#editCheckExpData input[type='radio']:checked");
		if (selected != null) {
			var value = selected.parent().parent().prev().text();
			value = value.replace("zł", "");
			var name = selected.parent().parent().prev().prev().text();

			$("#expensesValueData").val($.trim(value));

			$("#expensesListData option").each(function() {
				if ($(this).text() == name) {
					$(this).attr('selected', 'selected');
				}
			});
		}

	});
	$(document).on('change', '#editCheckIncData', function() {
		var selected = $("#editCheckIncData input[type='radio']:checked");
		if (selected != null) {
			var value = selected.parent().parent().prev().text();
			value = value.replace("zł", "");
			var name = selected.parent().parent().prev().prev().text();
			
			$("#incomeValueData").val($.trim(value));

			$("#incomeListData option").each(function() {
				if ($(this).text() == name) {
					$(this).attr('selected', 'selected');
				}
			});
		}

	});

	// dopisac zerowanie listy jesli funkcja wybierz na filtr
}
function showList(list, def) {
	var edit = "#edit".concat(def);
	var del = "#del".concat(def);
	var add = "#add".concat(def);
	if ($(list).val() == -1 || $(list).val() == null) {
		$(edit).hide();
		$(del).hide();
		$(add).show();
	} else {
		$(edit).show();
		$(del).show();
		$(add).hide();

	}

}
function showFilter(list, filter) {

	if ($(list).val() == -1 || $("#optionExp").is(":hidden")) {
		$(filter).hide();
	} else
		$(filter).show();

}
function loadAjax() {
	$.ajax({
		type : "POST",
		url : "lib/lib.php",
		data : {
			value : "date",

		},

		success : function(msg) {
			showError("");

			$("#dateButton").before(msg); // ustawienie daty
			reload(""); // ladowanie rowniez danych bazy

		},
		error : function() {
			showError("Blad polaczenia z serwerem!");
		}
	})

}

function loadAjaxList(list, selectList) {

	$.ajax({
		type : "POST",
		url : "lib/lib.php",
		data : {
			loadDefList : list,

		},

		success : function(msg) {
			showError("");

			filter = selectList + "Filter";
			data = selectList + "Data";
			$(data).children('option:not(:first)').remove();
			$(data).append(msg);
			$(filter).children('option:not(:first)').remove();
			$(filter).append(msg);
			$(selectList).children('option:not(:first)').remove();
			$(selectList).append(msg);
			showList("#expensesList", "ExpList");
			showList("#incomeList", "IncList");

		},
		error : function() {
			showError("Blad polaczenia z serwerem!");
		}
	})
}

function loadAjaxData(data, tableData, button, filter) {

	$
			.ajax({
				type : "POST",
				url : "lib/lib.php",
				dataType : 'json',
				data : {
					loadData : data,
					button : button,
					filter : filter,
				},

				success : function(msg) {

					showError("");
					if (msg[0] == "") {
						$(tableData).parent().find("#remove").remove();
						msg[0] = "<tr id=\"remove\"><td colspan=\"5\" >Brak Rekordów!</td></tr>";

					}
					while ($(tableData).parent().find("#remove").length > 0) {

						$(tableData).parent().find("#remove").remove();
					}

					$(tableData).after(msg[0]);
					score(tableData, msg[1]);
					$("#account").html(msg[2]);
				},
				error : function() {
					showError("Blad polaczenia z serwerem!");
				}
			});

}
function score(data, value) {
	if (data.indexOf("expenses") > -1) {
		$("#expensesScore").html(value);
	} else

		$("#incomeScore").html(value);

}
function reload(div, value) {

	if (div.indexOf("List") > -1) {
		if (div.indexOf("Exp") > -1) {
			loadAjaxList("Expenses", "#expensesList");

		} else {
			loadAjaxList("Income", "#incomeList");

		}
	} else if (div.indexOf("Data") > -1) {
		if (div.indexOf("Exp") > -1) {
			loadAjaxData("ExpensesData", "#expensesData");
		} else
			loadAjaxData("IncomeData", "#incomeData");
	} else if (div == "dateButton") {

		loadAjaxData("ExpensesData", "#expensesData", div, value);
		loadAjaxData("IncomeData", "#incomeData", div, value);

	} else if (div.indexOf("Filter") > -1) {
		if (div.indexOf("exp") > -1) {
			loadAjaxData("ExpensesData", "#expensesData", div, value);
		} else
			loadAjaxData("IncomeData", "#incomeData", div, value);
	} else {

		loadAjaxData("ExpensesData", "#expensesData");
		loadAjaxData("IncomeData", "#incomeData");
		loadAjaxList("Expenses", "#expensesList");
		loadAjaxList("Income", "#incomeList");
	}

}
function useAjaxList(value, button, id) {
	// dla definicji list - edytuj i usun i danych dodaj

	$.ajax({

		type : "POST",
		url : "lib/lib.php",
		data : {
			button : button,
			id : id,
			newValue : $(value).val(),
		}

	}).done(function(msg) {
		showError("");

		$(value).val("");

		reload(button, "");
		if (button == "editExpList")
			reload("ExpData", "");
		else if (button == "editIncList")
			reload("IncData", "");
	}).fail(function() {
		showError("Blad polaczenia z serwerem!");

	});

}

function useAjaxData(value, button, id, idList) {
	// dla danych edytowanie i usuwanie
	$.ajax({

		type : "POST",
		url : "lib/lib.php",
		data : {
			button : button,
			id : id,
			idList : idList,
			newValue : $(value).val(),
		},

		success : function() {
			showError("");
			$(value).val("");

			reload(button, "");
		},
		error : function() {
			showError("Blad polaczenia z serwerem!");
		}
	});

}
function changeNameFilter(filter, value) {

	if (filter == "reset") {
		$("#expensesButtonFilter").val("Filtruj");
		$("#incomeButtonFilter").val("Filtruj");
		$("#expensesListFilter").val(-1);
		$("#incomeListFilter").val(-1);

	} else if (filter.val() == "Filtruj") {
		$(filter).val("Usuń Filtr");

		reload(filter.attr("id"), value.val());

	} else if (filter.val() == "Usuń Filtr") {

		reload("dateButton", "");
		$(filter).val("Filtruj");
		value.val(-1);
	}
}

function click() {

	$("#addExpList").click(function() {

		if ($("#newExpensesList").val() != "") {
			useAjaxList("#newExpensesList", "addExpList", "");

		} else
			showError("Wartosc nie moze byc pusta!");
	});

	$("#editExpList").click(
			function() {
				if ($("#newExpensesList").val() != "") {
					useAjaxList("#newExpensesList", "editExpList", $(
							"#expensesList").val());

				} else
					showError("Wartosc nie moze byc pusta!");
			});
	$("#delExpList").click(
			function() {
				useAjaxList("#newExpensesList", "delExpList",
						$("#expensesList").val());

			});
	$("#addIncList").click(function() {
		if ($("#newIncomeList").val() != "") {
			useAjaxList("#newIncomeList", "addIncList", "")

		} else
			showError("Wartosc nie moze byc pusta!");
	});

	$("#editIncList").click(
			function() {
				if ($("#newIncomeList").val() != "") {
					useAjaxList("#newIncomeList", "editIncList", $(
							"#incomeList").val());

				} else
					showError("Wartosc nie moze byc pusta!");
			});
	$("#delIncList").click(function() {
		useAjaxList("#newIncomeList", "delIncList", $("#incomeList").val());

	});
	$("#dateButton").click(function() {
		var temp = $("#year").val() + "-" + $("#month").val();
		var date = new Date();
		var month = date.getMonth() + 1;
		changeNameFilter("reset");
		month = month.toString();
		if (month.length == 1)
			month = "0" + month;

		if ($("#year").val() == date.getYear() || $("#month").val() == month) {
			$("#optionExp").show();
			$("#optionInc").show();
			$("#expensesFilter").show();
			$("#incomeFilter").show();
		} else {
			$("#expensesFilter").hide();
			$("#incomeFilter").hide();
			$("#optionExp").hide();
			$("#optionInc").hide();

		}
		reload("dateButton", temp);
	});

	$("#expensesButtonFilter").click(
			function() {
				if ($("#expensesListFilter").val() != -1) {

					changeNameFilter($("#expensesButtonFilter"),
							$("#expensesListFilter"));

				} else
					showError("Wybierz pozycje z listy!");
			});
	$("#addExpData").click(
			function() {
				if ($("#expensesListData").val() != -1) {
					if ($("#expensesValueData").val() != "") {
						if (isNumber($("#expensesValueData").val())) {
							useAjaxData("#expensesValueData", "addExpData", $(
									"#expensesListData").val(), "");
							$("#expensesListData").val(-1);
						} else
							showError("Wartosc nie jest liczba!");
					} else
						showError("Wartosc nie moze byc pusta!");
				} else
					showError("Wybierz pozycje z listy!");
			});
	$("#editExpData").click(
			function() {
				var selected = $(
						"#editCheckExpData input[type='radio']:checked").val();
				if (selected != null) {
					if ($("#expensesListData").val() != -1) {
						if ($("#expensesValueData").val() != "")
							if (isNumber($("#expensesValueData").val()))
								useAjaxData("#expensesValueData",
										"editExpData", selected, $(
												"#expensesListData").val());
							else
								showError("Wartosc nie jest liczba!");
						else
							showError("Wartosc nie moze byc pusta!");
					} else
						showError("Wybierz pozycje z listy!");
				} else
					showError("Wybierz pozycje do edycji!");
			});

	$("#delExpData").click(function() {

		var selectedVal = [];
		$("#delCheckExpData input[type='checkbox']:checked").each(function() {
			selectedVal.push($(this).attr('value'));
		});

		if (selectedVal != null) {

			useAjaxData("#expensesValueData", "delExpData", selectedVal, "");

		} else
			showError("Wybierz pozycje do usuniecia!");

	});
	$("#incomeButtonFilter").click(function() {
		if ($("#incomeListFilter").val() != -1)
			changeNameFilter($("#incomeButtonFilter"), $("#incomeListFilter"));
		else
			showError("Wybierz pozycje z listy!");
	});
	$("#addIncData").click(
			function() {
				if ($("#incomeListData").val() != -1) {
					if ($("#incomeValueData").val() != "") {
						if (isNumber($("#incomeValueData").val())) {
							useAjaxData("#incomeValueData", "addIncData", $(
									"#incomeListData").val());
							$("#incomeListData").val(-1);
						} else
							showError("Wartosc nie jest liczba!");
					} else
						showError("Wartosc nie moze byc pusta!");
				} else
					showError("Wybierz pozycje z listy!");
			});
	$("#editIncData").click(
			function() {
				var selected = $(
						"#editCheckincData input[type='radio']:checked").val();
				if (selected != null) {
					if ($("#incomeListData").val() != -1) {
						if ($("#incomeValueData").val() != "")
							if (isNumber($("#incomeValueData").val()))
								useAjaxData("#incomeValueData", "editIncData",
										selected, $("#incomeListData").val());
							else
								showError("Wartosc nie jest liczba!");
						else
							showError("Wartosc nie moze byc pusta!");
					} else
						showError("Wybierz pozycje z listy!");
				} else
					showError("Wybierz pozycje do edycji!");
			});
	$("#delIncData").click(function() {
		var selectedVal = [];
		$("#delCheckIncData input[type='checkbox']:checked").each(function() {
			selectedVal.push($(this).attr('value'));
		});

		if (selectedVal != null) {

			useAjaxData("#incomeValueData", "delIncData", selectedVal, "");

		} else
			showError("Wybierz pozycje do usuniecia!");

	});

}
function showError(text) {

	$("#errorMessage").text(text);
}
function isNumber(value) {

	if (!isNaN(parseFloat(value)) && isFinite(value) && value > 0) {

		return true;

	} else
		return false;

}
