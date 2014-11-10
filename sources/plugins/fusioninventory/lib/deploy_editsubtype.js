/*
 * Edit a subtype element.
 */
function edit_subtype(subtype, order_id, _rand,el) {

   //remove all border to previous selected item (remove classes)
   Ext.select('#table_' + subtype + '_'+ _rand +' tr').removeClass('selected');


   var params = {
      'subtype'   : subtype,
      'orders_id' : order_id,
      'rand'      : _rand,
      'mode'      : 'create'
   }

   var row = null;
   if (el) {
      // get parent row of the selected element
      row = jQuery(el).parents('tr:first')
   }

   if (row) {
      //add border to selected index (add class)
      row.addClass('selected');
      params['index'] = row.index();
      // change mode to edit
      params['mode'] = 'edit';
   }

   //scroll to edit form
   document.getElementById('th_title_' + subtype + '_' + _rand).scrollIntoView();

   //show and load form
   Ext.get(subtype + 's_block' + _rand).setDisplayed('block');
   Ext.get(subtype + 's_block' + _rand).load({
      'url': '../ajax/deploypackage_form.php',
      'scripts': true,
      'params' : params
   });
}

/*
 * Create a new subtype element.
 * This method just override *edit_subtype* with a null element.
 */
function new_subtype(subtype, order_id, _rand) {
   edit_subtype(subtype, order_id, _rand, null);
}
