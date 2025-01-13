<template>
  <div class="hotel-portfolio">
    <!-- En-tête avec navigation -->
    <header class="header">
      <nav class="navigation">
        <div class="logo">
          <img :src="hotelInfo.logoUrl" :alt="hotelInfo.name">
        </div>
        <div class="nav-links">
          <a v-for="section in sections"
             :key="section.id"
             :href="'#' + section.id"
             @click.prevent="scrollToSection(section.id)">
            {{ section.name }}
          </a>
        </div>
        <div class="booking-button">
          <button @click="openBookingModal">Réserver maintenant</button>
        </div>
      </nav>
    </header>

    <!-- Hero Section avec Carousel -->
    <section id="hero" class="hero-section">
      <div class="carousel">
        <transition-group name="fade">
          <img v-for="(image, index) in heroImages"
               :key="image.id"
               v-show="currentImageIndex === index"
               :src="image.url"
               :alt="image.alt">
        </transition-group>
        <div class="carousel-controls">
          <button @click="prevImage">←</button>
          <button @click="nextImage">→</button>
        </div>
      </div>
      <div class="hero-content">
        <h1>{{ hotelInfo.name }}</h1>
        <p>{{ hotelInfo.tagline }}</p>
      </div>
    </section>

    <!-- Section À propos -->
    <section id="about" class="about-section">
      <h2>À propos de notre hôtel</h2>
      <div class="about-content">
        <div class="about-text">
          <p>{{ hotelInfo.description }}</p>
        </div>
        <div class="key-features">
          <div v-for="feature in hotelInfo.features"
               :key="feature.id"
               class="feature">
            <i :class="feature.icon"></i>
            <h3>{{ feature.title }}</h3>
            <p>{{ feature.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Section Chambres -->
    <section id="rooms" class="rooms-section">
      <h2>Nos Chambres</h2>
      <div class="rooms-grid">
        <div v-for="room in rooms"
             :key="room.id"
             class="room-card"
             @click="openRoomDetails(room)">
          <img :src="room.mainImage" :alt="room.name">
          <div class="room-info">
            <h3>{{ room.name }}</h3>
            <p>{{ room.shortDescription }}</p>
            <div class="room-features">
              <span v-for="feature in room.features"
                    :key="feature">{{ feature }}</span>
            </div>
            <div class="room-price">
              À partir de {{ room.price }}€ / nuit
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Section Services -->
    <section id="services" class="services-section">
      <h2>Nos Services</h2>
      <div class="services-grid">
        <div v-for="service in services"
             :key="service.id"
             class="service-card">
          <i :class="service.icon"></i>
          <h3>{{ service.name }}</h3>
          <p>{{ service.description }}</p>
        </div>
      </div>
    </section>

    <!-- Section Galerie -->
    <section id="gallery" class="gallery-section">
      <h2>Galerie</h2>
      <div class="gallery-grid">
        <div v-for="image in galleryImages"
             :key="image.id"
             class="gallery-item"
             @click="openGalleryModal(image)">
          <img :src="image.thumbnail" :alt="image.alt">
        </div>
      </div>
    </section>

    <!-- Section Avis -->
    <section id="reviews" class="reviews-section">
      <h2>Avis de nos clients</h2>
      <div class="reviews-carousel">
        <transition-group name="slide">
          <div v-for="review in displayedReviews"
               :key="review.id"
               class="review-card">
            <div class="review-rating">
              <i v-for="n in 5"
                 :key="n"
                 :class="['star', n <= review.rating ? 'filled' : '']"></i>
            </div>
            <p class="review-text">{{ review.text }}</p>
            <div class="review-author">
              <img :src="review.authorImage" :alt="review.authorName">
              <span>{{ review.authorName }}</span>
            </div>
          </div>
        </transition-group>
      </div>
    </section>

    <!-- Section Contact -->
    <section id="contact" class="contact-section">
      <h2>Contact</h2>
      <div class="contact-content">
        <div class="contact-info">
          <div class="address">
            <h3>Adresse</h3>
            <p>{{ hotelInfo.address }}</p>
          </div>
          <div class="phone">
            <h3>Téléphone</h3>
            <p>{{ hotelInfo.phone }}</p>
          </div>
          <div class="email">
            <h3>Email</h3>
            <p>{{ hotelInfo.email }}</p>
          </div>
        </div>
        <form class="contact-form" @submit.prevent="submitContactForm">
          <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" id="name" v-model="contactForm.name" required>
          </div>
          <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" v-model="contactForm.email" required>
          </div>
          <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" v-model="contactForm.message" required></textarea>
          </div>
          <button type="submit">Envoyer</button>
        </form>
      </div>
    </section>

    <!-- Modals -->
    <div v-if="showBookingModal" class="modal booking-modal">
      <div class="modal-content">
        <button class="close-modal" @click="closeBookingModal">&times;</button>
        <h2>Réserver une chambre</h2>
        <form @submit.prevent="submitBooking">
          <div class="form-group">
            <label for="checkIn">Arrivée</label>
            <input type="date" id="checkIn" v-model="bookingForm.checkIn">
          </div>
          <div class="form-group">
            <label for="checkOut">Départ</label>
            <input type="date" id="checkOut" v-model="bookingForm.checkOut">
          </div>
          <div class="form-group">
            <label for="guests">Nombre de personnes</label>
            <select id="guests" v-model="bookingForm.guests">
              <option v-for="n in 4" :key="n" :value="n">{{ n }}</option>
            </select>
          </div>
          <button type="submit">Confirmer la réservation</button>
        </form>
      </div>
    </div>

    <div v-if="showGalleryModal" class="modal gallery-modal">
      <div class="modal-content">
        <button class="close-modal" @click="closeGalleryModal">&times;</button>
        <img :src="selectedImage.fullSize" :alt="selectedImage.alt">
      </div>
    </div>

    <div v-if="showRoomDetailsModal" class="modal room-details-modal">
      <div class="modal-content">
        <button class="close-modal" @click="closeRoomDetailsModal">&times;</button>
        <h2>{{ selectedRoom.name }}</h2>
        <img :src="selectedRoom.mainImage" :alt="selectedRoom.name">
        <p>{{ selectedRoom.description }}</p>
        <div class="room-features">
          <span v-for="feature in selectedRoom.features" :key="feature">{{ feature }}</span>
        </div>
        <div class="room-price">
          À partir de {{ selectedRoom.price }}€ / nuit
        </div>
        <button @click="openBookingModal">Réserver maintenant</button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HotelPortfolio',
  data() {
    return {
      hotelInfo: {
        name: 'Grand Hôtel Palace',
        tagline: 'Une expérience de luxe inoubliable',
        logoUrl: 'https://via.placeholder.com/150',
        description: 'Notre établissement de luxe vous accueille dans un cadre exceptionnel...',
        features: [
          { id: 1, icon: 'fa-spa', title: 'Spa', description: 'Un espace bien-être de 500m²' },
          { id: 2, icon: 'fa-restaurant', title: 'Restaurant', description: 'Cuisine gastronomique' },
          // ... autres caractéristiques
        ],
        address: '123 Avenue du Luxe, 75008 Paris',
        phone: '+33 1 23 45 67 89',
        email: 'contact@grandhotelpalace.com'
      },
      heroImages: [
        { id: 1, url: 'https://via.placeholder.com/1200x600', alt: 'Façade de l\'hôtel' },
        { id: 2, url: 'https://via.placeholder.com/1200x600', alt: 'Suite présidentielle' },
        // ... autres images
      ],
      currentImageIndex: 0,
      rooms: [
        {
          id: 1,
          name: 'Suite Présidentielle',
          mainImage: 'https://via.placeholder.com/300x200',
          shortDescription: 'Notre suite la plus luxueuse avec vue panoramique',
          features: ['Vue mer', 'Jacuzzi privé', 'Butler'],
          price: 1500
        },
        // ... autres chambres
      ],
      services: [
        {
          id: 1,
          icon: 'fa-concierge-bell',
          name: 'Conciergerie 24/7',
          description: 'À votre service jour et nuit'
        },
        // ... autres services
      ],
      galleryImages: [
        {
          id: 1,
          thumbnail: 'https://via.placeholder.com/150',
          fullSize: 'https://via.placeholder.com/600x400',
          alt: 'Restaurant gastronomique'
        },
        // ... autres images
      ],
      reviews: [
        {
          id: 1,
          rating: 5,
          text: 'Une expérience exceptionnelle, le service est impeccable !',
          authorName: 'Jean Dupont',
          authorImage: 'https://via.placeholder.com/50'
        },
        // ... autres avis
      ],
      sections: [
        { id: 'about', name: 'À propos' },
        { id: 'rooms', name: 'Chambres' },
        { id: 'services', name: 'Services' },
        { id: 'gallery', name: 'Galerie' },
        { id: 'reviews', name: 'Avis' },
        { id: 'contact', name: 'Contact' }
      ],
      contactForm: {
        name: '',
        email: '',
        message: ''
      },
      bookingForm: {
        checkIn: '',
        checkOut: '',
        guests: 1
      },
      showBookingModal: false,
      showGalleryModal: false,
      showRoomDetailsModal: false,
      selectedImage: null,
      selectedRoom: null,
      displayedReviews: []
    }
  },
  methods: {
    scrollToSection(sectionId) {
      const element = document.getElementById(sectionId)
      element.scrollIntoView({ behavior: 'smooth' })
    },
    nextImage() {
      this.currentImageIndex = (this.currentImageIndex + 1) % this.heroImages.length
    },
    prevImage() {
      this.currentImageIndex = (this.currentImageIndex - 1 + this.heroImages.length) % this.heroImages.length
    },
    openBookingModal() {
      this.showBookingModal = true
      document.body.style.overflow = 'hidden'
    },
    closeBookingModal() {
      this.showBookingModal = false
      document.body.style.overflow = 'auto'
    },
    openGalleryModal(image) {
      this.selectedImage = image
      this.showGalleryModal = true
      document.body.style.overflow = 'hidden'
    },
    closeGalleryModal() {
      this.showGalleryModal = false
      document.body.style.overflow = 'auto'
    },
    openRoomDetails(room) {
      this.selectedRoom = room
      this.showRoomDetailsModal = true
      document.body.style.overflow = 'hidden'
    },
    closeRoomDetailsModal() {
      this.showRoomDetailsModal = false
      document.body.style.overflow = 'auto'
    },
    submitContactForm() {
      // Logique d'envoi du formulaire de contact
      console.log('Contact form submitted:', this.contactForm)
      // Réinitialiser le formulaire
      this.contactForm = { name: '', email: '', message: '' }
    },
    submitBooking() {
      // Logique de réservation
      console.log('Booking submitted:', this.bookingForm)
      this.closeBookingModal()
    },
    rotateReviews() {
      // Logique de rotation des avis
      const reviewsToShow = 3
      const startIndex = Math.floor(Math.random() * (this.reviews.length - reviewsToShow + 1))
      this.displayedReviews = this.reviews.slice(startIndex, startIndex + reviewsToShow)
    }
  },
  mounted() {
    // Démarrer le carousel automatique
    setInterval(this.nextImage, 5000)

    // Démarrer la rotation des avis
    this.rotateReviews()
    setInterval(this.rotateReviews, 10000)
  },
  beforeDestroy() {
    // Nettoyer les intervalles si nécessaire
    clearInterval(this.carouselInterval)
    clearInterval(this.reviewsInterval)
  }
}
</script>

<style scoped>
.hotel-portfolio {
  max-width: 100%;
  overflow-x: hidden;
}

/* Header styles */
.header {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  z-index: 1000;
  background: rgba(255, 255, 255, 0.95);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navigation {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

/* Hero section styles */
.hero-section {
  height: 100vh;
  position: relative;
}

.carousel {
  height: 100%;
  position: relative;
}

.carousel img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.hero-content {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
  color: white;
  z-index: 2;
}

/* Room cards styles */
.rooms-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  padding: 2rem;
}

.room-card {
  border: 1px solid #ddd;
  border-radius: 8px;
  overflow: hidden;
  transition: transform 0.3s;
}

.room-card:hover {
  transform: scale(1.05);
}

.room-card img {
  width: 100%;
  height: auto;
}

.room-info {
  padding: 1rem;
}

.room-features {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 1rem;
}

.room-features span {
  background-color: #f0f0f0;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
}

.room-price {
  margin-top: 1rem;
  font-weight: bold;
}

/* Modal styles */
.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1001;
}

.modal-content {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  position: relative;
}

.close-modal {
  position: absolute;
  top: 1rem;
  right: 1rem;
  font-size: 1.5rem;
  background: none;
  border: none;
  cursor: pointer;
}

/* Additional styles for better UX/UI */
.booking-button button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.booking-button button:hover {
  background-color: #0056b3;
}

.carousel-controls button {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  z-index: 3;
}

.carousel-controls button:first-child {
  left: 1rem;
}

.carousel-controls button:last-child {
  right: 1rem;
}

.gallery-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
  gap: 1rem;
  padding: 2rem;
}

.gallery-item {
  cursor: pointer;
  transition: transform 0.3s;
}

.gallery-item:hover {
  transform: scale(1.05);
}

.gallery-item img {
  width: 100%;
  height: auto;
  border-radius: 8px;
}

.reviews-carousel {
  display: flex;
  overflow-x: auto;
  gap: 1rem;
  padding: 2rem;
}

.review-card {
  background: #f9f9f9;
  padding: 1rem;
  border-radius: 8px;
  flex: 0 0 calc(33.333% - 1rem);
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.review-rating {
  display: flex;
  gap: 0.25rem;
}

.review-rating .star {
  color: #ddd;
}

.review-rating .star.filled {
  color: #ffc107;
}

.review-text {
  margin: 1rem 0;
}

.review-author {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.review-author img {
  border-radius: 50%;
}

.contact-content {
  display: flex;
  justify-content: space-between;
  padding: 2rem;
}

.contact-info {
  flex: 1;
}

.contact-form {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.contact-form button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.contact-form button:hover {
  background-color: #0056b3;
}
</style>
