function check_image(max, acc, msg, err)
{
	var max = (max !== undefined) ? max : 2048;
	var acc = (acc !== undefined) ? acc : 'jpe?g|gif|png';
	var msg = (msg !== undefined) ? msg : 'File too big!';
	var err = (err !== undefined) ? err : 'Wrong file format!';

	var span = document.querySelector('output > span');
	
	var error = document.querySelector('aside.error');
	if (error != null) {
		document.querySelector('.data-file > aside').classList.remove('error');
	}
	//document.querySelector('button.sub').removeAttribute('disabled');

	var view = document.querySelector('.view');
	if (view != null) {
		view.parentNode.removeChild(view);
	}

	//$("#form-data").unbind('submit');

	try {

		var file = document.getElementById('image').files[0];

		if (file)
		{
			var acc = new RegExp(acc, 'i');
			var ext = file.name.split('.').pop();
			if (acc.test(ext))
			{
				var legend = document.querySelector('.data-file > legend');
				if (legend != null) {
					document.querySelector('.data-file > legend').style.visibility = 'hidden';
				}

				var elem = document.createElement('div');
					elem.className = 'view';
					elem.innerHTML = '<img src="" alt="" />';

				var out = document.querySelector('output');
					out.insertBefore(elem, out.firstChild);

				var name = file.name.substr( 0, file.name.length - (ext.length + 1));
				var nameFirst = file.name.substr( 0, 27);
				var nameLast = file.name.substr( - 9, file.name.length - 9);

				if (file.name.length > 42) {
					span.innerHTML = '<b>' + nameFirst + ' .... <i>.' + ext + '</i></b>';
				} else {
					span.innerHTML = '<b>' + name + '<i>.' + ext + '</i></b>';
				}

				var Kbyte = (Math.round(file.size * 100 / 1024) / 100).toString();
				if (Kbyte > max) {
					document.querySelector('.view').classList.add('error');
					span.innerHTML = '<i>' + msg + '</i>';
					//document.querySelector('button.sub').setAttribute('disabled', 'disabled');
					//$("#form-data").submit( function (e) {
					//	e.preventDefault();
					//});
				}

				var el = document.querySelector('.view > img');

				if (typeof file.getAsDataURL == 'function')
				{
					if (file.getAsDataURL().substr(0, 11) == 'data:image/') {
						el.src = file.getAsDataURL();
					} else {
						el.src = 'data:application/octet-stream;base64,R0lGODlhAQABAIAAAMDAwAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==';
					}
				}
				else
				{
					var reader = new FileReader();
					reader.onloadend = function(evt) {
						if (evt.target.readyState == FileReader.DONE) {
							el.src = evt.target.result;
						}
					};

					var read;
					if (file.slice) {
						read = file.slice(0, file.size);
					} else if (file.webkitSlice) {
						read = file.webkitSlice(0, file.size);
					} else if (file.mozSlice) {
						read = file.mozSlice(0, file.size);
					}
					reader.readAsDataURL(read);
				}

			} else {
				span.innerHTML = '<i>' + err + '</i>';
				//document.querySelector('button.sub').setAttribute('disabled', 'disabled');
				//$("#form-data").submit( function (e) {
				//	e.preventDefault();
				//});
			}
		}
	}
	catch(e) {
		var file = document.getElementById('image').value;
		file = file.replace(/\\/g, "/").split('/').pop();
		span.innerHTML = file;
	}
}