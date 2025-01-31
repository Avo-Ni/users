<template>
  <header>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-absolute bg-primary shadow-lg fixed-top">
      <div class="container">
        <a class="navbar-brand text-warning animate__animated animate__fadeInDown" href="#home">Calme et Bien Etre</a>
        <button
          class="navbar-toggler"
          type="button"
          @click="toggleNavbar"
          aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div :class="['collapse', 'navbar-collapse', { show: isNavbarOpen }]"><ul class="navbar-nav mx-auto">
            <li class="nav-item" v-for="(link, index) in navLinks" :key="index">
              <a
                class="nav-link text-warning animate__animated animate__fadeInUp"
                :href="link.path"
                @click.prevent="scrollToSection(link.path)"
                @mouseover="hoverLink($event)"
                @mouseleave="leaveLink($event)">
                {{ link.name }}
              </a>
            </li>
          </ul>
          <ul class="nav navbar-nav">
            <li v-for="(icon, index) in socialLinks" :key="index" class="nav-item">
              <a
                class="nav-link text-warning animate__animated animate__zoomIn"
                :href="icon.url"
                target="_blank">
                <i :class="icon.iconClass"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div id="home" class="page-header min-vh-100 d-flex align-items-center"
      :style="{
        backgroundImage: 'url(https://images.unsplash.com/photo-1520769945061-0a448c463865?auto=format&fit=crop&w=1950&q=80)',
      }">
      <span class="mask bg-gradient-dark opacity-5"></span>
      <div class="container text-center">
        <h1 class="text-warning mb-4 animate__animated animate__fadeInLeft">Prennez Soin de Vous</h1>
        <p class="text-light opacity-8 lead px-5 animate__animated animate__fadeInUp">
          The time is now for it be okay to be great. People in this world shun people for being nice.
        </p>
        <div class="buttons d-flex justify-content-center gap-3">
          <MaterialButton color="warning" class="mt-4 animate__animated animate__pulse animate__infinite" @click="scrollToSection('#about')">Get Started</MaterialButton>
          <MaterialButton color="none" class="text-warning shadow-none mt-4 animate__animated animate__fadeIn" @click="scrollToSection('#about')">Read more</MaterialButton>
        </div>
      </div>
    </div>
  </header>

  <section id="about" class="py-9 section-transition">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 my-auto">
          <h3>Read More About Us</h3>
          <p class="pe-5">
            Pain is what we go through as we become older. We get insulted by others, lose trust for those others. We get back stabbed by friends.
          </p>
          <a href="javascript:;" class="text-success icon-move-right" @click="scrollToSection('#services')">
            More about us <i class="fas fa-arrow-right text-sm ms-1"></i>
          </a>
        </div>
        <div class="col-lg-6 mt-lg-0 mt-5 ps-lg-0 ps-0">
          <AboutUsOption icon="mediation" content="It becomes harder for us to give others a hand.<br />We get our heart broken by people we love." />
          <AboutUsOption icon="settings_overscan" content="As we live, our hearts turn colder.<br />Cause pain is what we go through as we become older." />
          <AboutUsOption icon="token" content="When we lose family over time.<br />What else could rust the heart more over time? Blackgold." />
        </div>
      </div>
    </div>
  </section>

  <section>
    <div class="container py-4">
      <div class="row">
        <div class="col-lg-7 mx-auto d-flex justify-content-center flex-column">
          <h3 class="text-center">Contact us</h3>
          <form role="form" id="contact-form" method="post" autocomplete="off">
            <div class="card-body">
              <div class="row">
                <div class="col-md-6">
                  <MaterialInput
                    class="input-group-dynamic mb-4"
                    :label="{ text: 'First Name', class: 'form-label' }"
                    type="text"
                  />
                </div>
                <div class="col-md-6 ps-2">
                  <MaterialInput
                    class="input-group-dynamic"
                    :label="{ text: 'Last Name', class: 'form-label' }"
                    type="text"
                  />
                </div>
              </div>
              <div class="mb-4">
                <MaterialInput
                  class="input-group-dynamic"
                  :label="{ text: 'Email Address', class: 'form-label' }"
                  type="email"
                />
              </div>
              <MaterialTextArea
                class="input-group-static mb-4"
                id="message"
                :rows="4"
                >Your message</MaterialTextArea
              >
            </div>
            <div class="row">
              <div class="col-md-12">
                <MaterialSwitch
                  class="mb-4 d-flex align-items-center"
                  id="flexSwitchCheckDefault"
                  checked
                  labelClass="ms-3 mb-0"
                >
                  I agree to the
                  <a href="javascript:;" class="text-dark"
                    ><u>Terms and Conditions</u></a
                  >.
                </MaterialSwitch>

                <div class="col-md-12">
                  <MaterialButton
                    type="submit"
                    variant="gradient"
                    color="dark"
                    fullWidth
                    >Send Message</MaterialButton
                  >
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      isNavbarOpen: false,
      navLinks: [
        { name: 'Home', path: '#home' },
        { name: 'About Us', path: '#about' },
        { name: 'Contact Us', path: '#contact' }
      ],
      socialLinks: [
        { url: 'https://twitter.com/CreativeTim', iconClass: 'fab fa-twitter' },
        { url: 'https://www.facebook.com/CreativeTim', iconClass: 'fab fa-facebook' },
        { url: 'https://www.instagram.com/CreativeTimOfficial', iconClass: 'fab fa-instagram' }
      ]
    };
  },
  methods: {
    toggleNavbar() {
      this.isNavbarOpen = !this.isNavbarOpen;
    },
    hoverLink(event) {
      event.target.classList.add('animate__pulse');
    },
    leaveLink(event) {
      event.target.classList.remove('animate__pulse');
    },
    scrollToSection(section) {
      document.querySelector(section).scrollIntoView({ behavior: 'smooth' });
    }
  }
};
</script>

<style>
@import 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css';

.navbar-toggler {
  transition: transform 0.3s ease-in-out;
}

.navbar-toggler:hover {
  transform: rotate(90deg);
}

.bg-primary {
  background-color: #88ff0069 !important;
}

.text-warning {
  color: #01a717c7 !important;
}

.section-transition {
  transition: all 0.5s ease-in-out;
}
</style>
