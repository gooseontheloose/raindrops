# Deploying Raindrops to Railway

This guide walks you through getting the Raindrops website live on Railway with working dynamic CMS settings and persistent logs.

## 1. Connect to GitHub
1. Log into your [Railway](https://railway.app/) dashboard.
2. Click **New Project** -> **Deploy from GitHub repo**.
3. Select your `raindrops` repository.

## 2. Setting Up the Storage Volume (CRUCIAL)
Because Railway rests the virtual machine on every deployment, your active CMS settings and ban appeals will be wiped unless you use a persistent volume. This is very easy to set up!
1. Go to your **Project Dashboard** on Railway.
2. Click **New** -> **Storage** -> **Volume**.
3. Create a new volume and name it `raindrops-data` (or anything you like).
4. Go back to your deployed **Website Service** settings.
5. In the **Volumes** tab (or Settings -> Volumes), attach your newly created volume. 
   - **Mount Path**: `/var/www/html/data`
6. Wait for the service to redeploy automatically.

## 3. Launch!
1. Go to the **Settings** tab of your service.
2. Under **Networking** -> **Public Networking**, click **Generate Domain**.
3. Click the generated link to visit your live site!

## Notes
- The default Staff Code is `RAINDROPS_STAFF`. Make sure to change this in `staff.html` and the `.php` files before going public if you want!
- The first time the site loads, it copies the initial `default_settings.json` into your active database. 
- Any CMS updates you make on the live site will be saved to the attached volume, meaning they will safely persist forever.
