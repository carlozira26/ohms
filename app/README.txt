npm run build
npx cap add android
npx cap open android

* In AndroidManifest.xml (HTTP Access)
<Application
	android:usesCleartextTraffic="true"
	tools:ignore="googleAppIndexingWarning">
	<uses-library
		android:name="org.apache:http.legacy"
		android:required="false"/>

* In capacitor.config.json
	"webDir" : "dist"