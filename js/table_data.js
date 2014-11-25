// Set text long column
function configColumn(allColumn) {
	for(i=3; i<=allColumn+2; i++) { // column table start on column 3rd
		if($('table.mbk tr td:nth-child(' + i + ')').width() >= 250) {
			$('table.mbk tr td:nth-child(' + i + ')').addClass('txtLong-col');
			if($('table.mbk tr td:nth-child(2)').width() > 50) {
				$('table.mbk tr td:nth-child(' + i + ')').removeClass('txtLong-col');
				break;
			}
		}
	}
}