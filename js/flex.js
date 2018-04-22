// CORS support enabled to allow POST to the server.
jQuery.support.cors = true
$(document).on('click', '.user-input', function () {
  // Check corresponding radio button when input is entered.
  radio_id = '#' + $(this).attr('id') + '-radio'
  $(radio_id).prop('checked', true)
  // Remove error messages when new input is entered.
  $('.general-error-message').css('display', 'none')
  removeErrorMessages()
})
$(document).on('change', 'input[name="input"]', function () {
  // Remove error messages when new input is entered or radio is selected.
  removeErrorMessages()
})
$(document).on('click', '#clear-input', function () {
  // Clear error messages and user input when Clear button is clicked.
  $(':input[class="user-input"]').val('')
  // Set textbox word count to 0.
  $('#word-count').text('0')
  removeErrorMessages()
})
$(document).on('input', '#textbox', function () {
  // Update textbox word count when value changes.
  $('#word-count').text(countWords($(this).val()))
})
$(document).on('click', '#submit', function (e) {
  /*
	If no radio button is checked, display error messages
	else, validate user input.
	*/
  if (!$('input[name="input"]:checked').val()) {
    $('.general-error-message').css('display', 'block')
  } else {
    const value = $('input[name="input"]:checked').attr('id')
    initValidation(value)
  }
})
function countWords (string) {
  // Count words in textbox.
  return string.trim().split(/\s+/).length
}
function initValidation (value) {
  // Validate corresponding user input.
  if (value == 'url-radio') validateUrl($('#url').val())
  if (value == 'upload-radio') validateUpload($('#upload').val())
  if (value == 'textbox-radio') validateText($('#textbox').val())
}
function validateUrl (value) {
  // If not valid url or .txt url, display error message and prevent submission.
  const url_pattern = /^(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?\.txt$/
  if (!(value && url_pattern.test(value))) {
    addInvalidClass('#url')
  } else {
    // If valid, submit url link to server.
    showLoadingBar()
    removeInvalidClass('#url')
    var data = 'url=' + $('#url').val()
    submitToServer(data)
  }
}
function validateUpload (value) {
  // If no file is uploaded, display error message and prevent submission.
  var upload_pattern = /\.txt$/i
  if (!($('#upload')[0].files)) {
    addInvalidClass('#upload')
  } else {
  	// If file is > 4KB, display error message and prevent submission.
    if (!(value && upload_pattern.test(value) && Math.round($('#upload')[0].files[0].size / 1000) <= 4)) {
      addInvalidClass('#upload')
    } else {
      // If valid, submit file to server.
      removeInvalidClass('#upload')
      showLoadingBar()
      var formData = new FormData()
      formData.append('file', $('#upload')[0].files[0])
      $.ajax({
        type: "POST",
        url: "http://localhost:81/Flexi/flexiterm-demo/flex.php",
		    data: formData,
		    cache: false,
        contentType: false,
        processData: false,
        enctype: "multipart/form-data",
		    success: function (data) {
		    	hideLoadingBar()
		      if (data != 'Busy') window.open('http://localhost:81/Flexi/flexiterm-demo/results.php', '_self')
		      else alert('FlexiTerm is in use right now, please try again in a few minutes.')
        },
        error: function (error, xhr, status) {
          alert('Error occurred with upload')
        }
      })
    }
  }
}
function validateText (value) {
  // If word count > 600  display error message and prevent submission.
  if (!(value && value.trim() && countWords(value) <= 600)) {
    addInvalidClass('#textbox')
  } else {
  	// If valid, submit file to server.
    removeInvalidClass('#textbox')
    showLoadingBar()
    var data = 'textbox=' + $('#textbox').val()
    submitToServer(data)
  }
}
function submitToServer (data) {
  // Upload data to server.
  $.ajax({
    type: 'POST',
    url: 'http://localhost:81/Flexi/flexiterm-demo/flex.php',
    data: data,
    cache: false,
    success: function (data) {
      hideLoadingBar()
      /* if Server is not busy, load Results page
      		else, load display error alert
      */
      if (data != 'Busy') window.open('http://localhost:81/Flexi/flexiterm-demo/results.php', '_self')
      else alert('FlexiTerm is in use right now, please try again in a few minutes.')
    },
    error: function (error, xhr, status) {
      console.log(error, xhr, status)
    }
  })
}
function showLoadingBar () {
  $('.overlay').css('display', 'block')
  $('.loading').css('display', 'block')
  $('.loading-text').css('display', 'block')
}
function hideLoadingBar () {
  $('.overlay').css('display', 'none')
  $('.loading').css('display', 'none')
  $('.loading-text').css('display', 'none')
}
function removeErrorMessages () {
  $('.invalid').removeClass('invalid')
  $('.error-message').css('display', 'none')
}
function addInvalidClass (id) {
  $(id).addClass('invalid')
  $(id + '-error').css('display', 'block')
}
function removeInvalidClass (id) {
  $(id).removeClass('invalid')
  $(id + '-error').css('display', 'none')
}
