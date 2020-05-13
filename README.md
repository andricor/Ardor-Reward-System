# Ardor-Reward-System
Reward your BuddyPress users with a real Ardor Blockchain Asset

The Ardor Reward System allows BuddyPress website owners to reward their users with an Asset created on the Ardor Platform.

The pre-requisites plugins are:
- BuddyPress
- myCred

The activation of the plugin will verify if these 2 plugins are installed. If not the plugin will not be activated.

The pre-requisites to allow the Ardor Reward System to work are:
- An asset has to be created on the Ardor Blockchain
- The issuer of the Asset has to have some Ignis in the wallet in order to pay the transaction fees
- The users Ardor Accounts need to be "active" (they must have performed at least one outgoing or one incoming transaction) in order to receive the rewards. 

All points collected with the myCred plugin will be converted 1 to 1 to the Asset created as a reward.

The reward is paid according to the schedule defined in the plugin settings page.

The rewards will be transferred at the users only after the "threshold" value (or multiples of it) has been passed. 

Please use with caution. This is a very first release and therefore don't keep too many Ignis in the account controlling the asset. Please also keep in mind that your passphrase is stored in the database in plain text, therefore be careful with who has access to your database.
