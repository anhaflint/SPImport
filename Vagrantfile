
Vagrant.configure("2") do |config|

   # Ubuntu
   config.vm.box = "precise64"
   config.vm.box_url = "http://files.vagrantup.com/precise64.box" #Box Location

   config.vm.provider :virtualbox do |virtualbox|
      virtualbox.customize ["modifyvm", :id, "--memory", "2048"]
   end

   # enable NFS mount
   config.vm.synced_folder ".", "/synced/", :nfs => true
   
   # Forward 8080 rquest to vagrant 80 port
   config.vm.network :forwarded_port, guest: 80, host: 8080

   # need a private network for NFS shares to work
   config.vm.network :private_network, ip: "192.168.50.110"
  
   # puppet config
   config.vm.provision "puppet" do |puppet|
      puppet.manifests_path = "manifests"
      puppet.manifest_file = "site.pp"
   end
end
