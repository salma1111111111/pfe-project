

<?php include 'header.php';


require_once 'admin/config/config.php';


?>


  <main id="main">
    
    <section id="contact" class="contact">
      <div class="container" data-aos="fade-up">

        <div class="section-title">
          <h2>Contactez-nous</h2>
          <p>nous sommes à votre écoute </p>
        </div>

        <div class="row">

          <div class="col-lg-5 d-flex align-items-stretch">
            <div class="info">
              <div class="address">
                <i class="bi bi-geo-alt"></i>
                <h4>Location:</h4>
                <p>xxxxxxxxxxx , sale</p>
              </div>

              <div class="email">
                <i class="bi bi-envelope"></i>
                <h4>Email:</h4>
                <p>info@example.com</p>
              </div>

              <div class="phone">
                <i class="bi bi-phone"></i>
                <h4>telephone:</h4>
                <p>+21200000000</p>
              </div>

              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d105799.62664679982!2d-6.842707370406099!3d34.03775699322962!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xda7695fc6d0ea0b%3A0xc54575dea3dd9353!2zU2Fsw6ksIE1vcm9jY28!5e0!3m2!1sen!2sus!4v1653930139466!5m2!1sen!2sus" width="400" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>            </div>

          </div>

          <div class="col-lg-7 mt-5 mt-lg-0 d-flex align-items-stretch">
            <form action="sendmesg.php" method="post" role="form" class="php-email-form">

              <div class="row">
                <div class="form-group col-md-6">
                  <label for="name">Nom</label>
                  <input type="text" name="name" class="form-control" id="name" required>
                </div>

                <div class="form-group col-md-6">
                  <label for="name">Email</label>
                  <input type="email" class="form-control" name="email" id="email" required>
                </div>
              </div>

              <div class="form-group">
                <label for="name">objet</label>
                <input type="text" class="form-control" name="objet" id="subject" required>
              </div>

              <div class="form-group">
                <label for="name">Message</label>
                <textarea class="form-control" name="message" rows="10" required></textarea>
              </div>

              <div class="form-group">
                <input type="submit"  class="form-control" name="submit" id="submit" required>
              </div>



              <!-- <div class="my-3">
                <div class="loading">Loading</div>
                <div class="error-message"></div>
                <div class="sent-message">Your message has been sent. Thank you!</div>
              </div>
              <div class="text-center"><button type="submit">envoyer</button></div> -->
              
            </form>
          </div>

        </div>

      </div>
    </section><!-- End Contact Section -->

  </main><!-- End #main -->

  <?php include 'footer.php';?>
