$(function() {
    $("#testimonial-add").validate({
        rules: {
            client_name: 'required',
            client_description: 'required',
            testimonial: 'required',
            custImages:{
                //required:true,
                extension: "jpg|jpeg|png|gif|JPG|JPEG|PNG|GIF"
            },
            custPdfs:{
                //required:true,
                extension: "pdf|PDF"
            }
         },
         messages: {
            custImages:{
                //required:"input type is required",                  
                extension:"Only .jpg, .png, .gif extensions supported"
            },
            custPdfs:{
                //required:"input type is required",                  
                extension:"Only .pdf extensions supported"
            }
         },
         submitHandler: function(form) {
            var testimonial_data = CKEDITOR.instances['testimonial'].getData();

            var formData = new FormData($("#testimonial-add")[0]);
            formData.append('testimonial_data', testimonial_data);

            $.ajax({
                type: "POST",
                url: BASE_URL+"TestimonialController/add_testimonial_detail",
                dataType: 'json',
                data: formData,
                cache: false,
                processData: false,
                contentType: false,
                success: function (res) {
                   var dataResult = JSON.parse(JSON.stringify(res));
                   if (dataResult.status == 200) {
                    swal({
                        title: "",
                        icon: "success",
                        text: res.msg,
                        buttons: false,
                    },
                    function(){window.location = dataResult.redirect })
                    }
                    else{
                        alert('An Error Occurred! Try again Later');
                    }
                }
            });
         }
    });
});

function delete_testimonial(id)
{
    var confirmation = confirm("are you sure you want to delete the testimonial ?");

    if(confirmation) {
        $.ajax({
            type:'post',
            dataType:'json',
            url: BASE_URL+"TestimonialController/delete_testimonial",
            data: {testimonial_id:id},
            beforeSend: function() {

            },
            success: function(response){
                if(response.status == 200) {
                    swal({
                        title: "",
                        icon: "error",
                        text: response.msg,
                        buttons: false,
                    },
                    function(){ $('#testimonialLists').DataTable().ajax.reload(); })
                }
            }
        });
    }
}

function DownloadAllTestimonialsCSV()
{	
		window.location.href=BASE_URL+'TestimonialController/DownloadAllTestimonialCSV';
}