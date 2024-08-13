<?php 

    require 'config/database.php';

    //get signup form data
    if(isset($_POST['submit'])){
        $firstname=filter_var($_POST['firstname'],FILTER_SANITIZE_SPECIAL_CHARS);
        $lastname=filter_var($_POST['lastname'],FILTER_SANITIZE_SPECIAL_CHARS);
        $username=filter_var($_POST['username'],FILTER_SANITIZE_SPECIAL_CHARS);
        $email=filter_var($_POST['email'],FILTER_VALIDATE_EMAIL);
        $createpassword=filter_var($_POST['createpassword'],FILTER_SANITIZE_SPECIAL_CHARS); 
        $confirmpassword=filter_var($_POST['confirmpassword'],FILTER_SANITIZE_SPECIAL_CHARS);
       $is_admin=filter_var($_POST['userrrole'],FILTER_SANITIZE_NUMBER_INT);
        $avatar=$_FILES['avatar'];
        
        //validate input
        if(!$firstname){
            $_SESSION['add-user']="Enter your first name";
        } elseif(!$lastname){
            $_SESSION['add-user']="Enter your last name";
        } elseif(!$username){
            $_SESSION['add-user']="Enter your username";
        } elseif(!$email){
            $_SESSION['add-user']="Enter a valid email";
        } elseif(strlen($createpassword)<8 || strlen($confirmpassword)<8){
            $_SESSION['add-user']="Password should be 8 or more characters long";
        } elseif(!$avatar['name']){
            $_SESSION['add-user']="Select an avatar";
        }
        else {
            //check password match
            if($createpassword!==$confirmpassword){
                $_SESSION['add-user']= "Passwords do not match";
            }
            else {
                $hashed_password=password_hash($createpassword,PASSWORD_DEFAULT);
                
                //check if username or email already exists in database
                $user_check_query= "SELECT * FROM users WHERE username='$username' OR email='$email'";
                $user_check_result=mysqli_query($connection,$user_check_query);
                if(mysqli_num_rows($user_check_result)>0){
                    $_SESSION['add-user']="Username or Email already exists";   
                }
                else {
                    //work on avatar
                    //rename avatar
                    $time=time();// making each image name unique
                    $avatar_name=$time.$avatar['name'];
                    $avatar_tmp_name=$avatar['tmp_name'];
                    $avatar_destination_path='../images/'.$avatar_name;

                    //make sure file is an image
                    $allowed_files=['png','jpeg','jpg'];
                    $extension=explode('.',$avatar_name);
                    $extension=end($extension);
                    if(in_array($extension,$allowed_files)){
                        if($avatar['size']<1000000){
                            move_uploaded_file($avatar_tmp_name,$avatar_destination_path);
                        }
                        else{
                            $_SESSION['add-user']='file size too big';
                        }
                } else{
                    $_SESSION['add-user']= 'File should be png,jpg or jpeg';
                }
            }
        }
         }
         if(isset($_SESSION['add-user'])){
            $_SESSION['add-user-data']= $_POST;
            header('location:'.ROOT_URL.'admin/add-user.php');
            die();
        } else {
            $insert_user_query="INSERT INTO users (firstname,lastname,username,email,password,avatar,is_admin) 
            VALUES ('$firstname','$lastname','$username','$email','$hashed_password','$avatar_name',0)";
            $insert_user_result=mysqli_query($connection,$insert_user_query);
            
            if(!mysqli_errno($connection)){
                //redirect to login page
                $_SESSION['add-user-success']="New user added successfully.";
                header('location:'.ROOT_URL.'admin/manage-users.php');
                die();
            }
        }
    }
    else {
//if button was not clicked go to signup
header('location:'.ROOT_URL.'add-user.php');
die();
    }
    