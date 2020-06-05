function fill_modal(id){
  var row = $("#row" + id.toString())[0];

  /* Filling fields with zero and then with values */
  var meno_upd = row.cells[4].textContent;
  $("input[name='meno_upd']")[0].value = "";
  $("input[name='meno_upd']")[0].value = meno_upd.toString();

  var datumOD_upd = row.cells[5].textContent;
  $("input[name='datumOD_upd']")[0].value = "";
  $("input[name='datumOD_upd']")[0].value = datumOD_upd.toString();

  var id_upd = $("input[name='upd_id']")[0].value = id.toString();

}

function fill_strany_modal(id){
  // alert(id);
  var id_upd = $("input[name='upd_strany_id']")[0].value = id.toString();

}
