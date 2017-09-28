<?php
  if (!osc_is_web_user_logged_in()) {
    _e("You must log in or register a new account in order to chat the advertiser");
?>
  <p class="contact_button">
    <strong><a href="<?php echo osc_user_login_url(); ?>"><?php _e('Login'); ?></a></strong> or <strong><a href="<?php echo osc_register_account_url(); ?>"><?php _e('Register for a free account'); ?></a></strong>
  </p>
<?php
    return;
  }

  $conn = getConnection() ;
  $message_room = $conn->osc_dbFetchResult("SELECT * FROM %st_message_room WHERE pk_i_message_room_id = %d AND fk_i_buyer_id = %d", DB_TABLE_PREFIX, intval(Params::getParam('message_room_id')), osc_logged_user_id());
  if(!isset($message_room['pk_i_message_room_id'])) {
    _e("This is not your private message!");
    return;
  }
  $item = Item::newInstance()->findByPrimaryKey(intval($message_room['fk_i_item_id']));
  View::newInstance()->_exportVariableToView('item', $item);
?>

<div style="padding: 20px;">
  <div style="margin: 8px; float: left; width: 56%; border: 1px solid rgb(234, 234, 234);">
    <div style="overflow: auto; margin: 4px; height: 512px; border: 1px solid rgb(234, 234, 234);">
    </div>
    <div style="margin: 4px">
      <script src="https://rawgit.com/jackmoore/autosize/master/dist/autosize.min.js"></script>
      <textarea id="message-box" style="width: 80%; resize: none"></textarea>
      <script>autosize($('#message-box'))</script>
      <button>Send</button>
    </div>
    <div style="margin: 4px">
      <input type="file" name="pic" accept="image/*">
    </div>
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <b>Title:</b> <br />
    <?php echo osc_item_title() ?> <br />
    <br />
    <b>Description:</b> <br />
    <?php echo osc_item_description() ?> <br />
    <br />
    <b>Price:</b> <br />
    <?php echo osc_item_formated_price(); ?> <br />
  </div>
  <div style="margin: 8px; padding: 8px; float: left; width: 36%; border: 1px solid rgb(234, 234, 234);">
    <input type="number" value="<?php echo (osc_item_price()/1000000) ?>"> <button>Make offer</button>
  </div>
  <div style="clear: both;"></div>
</div>

<?php
  $user_id = Params::getParam('buyer');
  $conn = getConnection();
  $user = $conn->osc_dbFetchResult("SELECT * FROM %st_user WHERE pk_i_id = %d", DB_TABLE_PREFIX, $user_id);
?>

<script src="<?php echo osc_plugin_url('private_message/js/firebase.js') . 'firebase.js' ?>"></script>
<script>
  // Initialize Firebase
  var config = {
    apiKey: "AIzaSyCDxUzO8HNZ0dJtxSFFow6lkRMyWKWzrWE",
    authDomain: "osclass-private-message-766aa.firebaseapp.com",
    databaseURL: "https://osclass-private-message-766aa.firebaseio.com",
    projectId: "osclass-private-message-766aa",
    storageBucket: "osclass-private-message-766aa.appspot.com",
    messagingSenderId: "506407180637"
  };
  firebase.initializeApp(config);

  function signInOrCreate(email, password) {
    firebase.auth().signInWithEmailAndPassword(email, password).catch(function(error) {
      // Handle Errors here.
      var errorCode = error.code;
      if (errorCode === 'auth/user-not-found') {
        firebase.auth().createUserWithEmailAndPassword(email, password).catch(function(error) {
          // Handle Errors here.
          var errorCode = error.code;
          var errorMessage = error.message;
          console.log('error create user');
          console.log(error);
        });
      }

      var errorMessage = error.message;
      console.log('error sign in');
      console.log(error);
    });
  }

  firebase.auth().onAuthStateChanged(function(user) {
    if (user) {
      // User is signed in.
      var displayName = user.displayName;
      var email = user.email;
      var emailVerified = user.emailVerified;
      var photoURL = user.photoURL;
      var isAnonymous = user.isAnonymous;
      var uid = user.uid;
      var providerData = user.providerData;
      
      console.log(user);
    } else {
      // User is signed out.
      // ...
    }
  });

  signInOrCreate("<?= $user['s_email'] ?>", "<?= $user['s_secret'] ?>");
</script>
