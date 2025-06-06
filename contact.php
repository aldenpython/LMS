<?php
// Include authentication functions
require_once 'be_auth.php';

// Include header
include 'inc_header_nav.php';
?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>Contact Us</h2>
        </div>
        <div class="card-body">
            <p>
                We'd love to hear from you! Whether you have a question about our courses, 
                need technical support, or want to provide feedback, please don't hesitate to reach out.
            </p>
            
            <div class="row">
                <div class="col">
                    <h3>Contact Information</h3>
                    <p><strong>Email:</strong> info@learningplatform.com</p>
                    <p><strong>Phone:</strong> +61 2 1234 5678</p>
                    <p><strong>Address:</strong> 123 Education Street, Sydney, NSW 2000, Australia</p>
                    <p><strong>Hours:</strong> Monday-Friday, 9:00 AM - 5:00 PM AEST</p>
                </div>
                
                <div class="col">
                    <h3>Send Us a Message</h3>
                    <form id="contact-form">
                        <div class="form-group">
                            <label for="name">Name:</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email:</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject:</label>
                            <input type="text" id="subject" name="subject" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message:</label>
                            <textarea id="message" name="message" class="form-control" rows="5" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </div>
                    </form>
                    
                    <div class="alert alert-info">
                        <p><strong>Note:</strong> This is a demo form and does not actually send messages.</p>
                    </div>
                </div>
            </div>
            
            <h3>Frequently Asked Questions</h3>
            <div class="faq">
                <h4>How do I enroll in a course?</h4>
                <p>
                    To enroll in a course, you need to create an account and log in. 
                    Then, browse the available courses and click the "Enroll" button on the course page.
                </p>
                
                <h4>How do I access my course materials?</h4>
                <p>
                    Once enrolled, you can access your course materials by logging in and navigating to "My Enrollments".
                </p>
                
                <h4>How do I view my grades?</h4>
                <p>
                    You can view your grades by logging in and navigating to "My Grades".
                </p>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'inc_footer.php';
?>