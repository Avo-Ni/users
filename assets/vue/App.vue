<template>
  <div id="app">
    <router-view></router-view>
  </div>
</template>

<script>
import RolePage from "./pages/RolePage.vue";
import UserPage from "./pages/UserPage.vue";
import ResourcePage from "./pages/ResourcePage.vue";
import PrivilegePage from "./pages/PrivilegePage.vue";

export default {
  name: 'App',
  components: {
    RolePage,
    UserPage,
    ResourcePage,
    PrivilegePage,
  }
};
</script>

<style scoped>
#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  color: #2c3e50;
  margin: 20px;
}
</style>
