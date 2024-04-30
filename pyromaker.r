#cleaning up the string in R

rm(list=ls())

cleanDNA<-function (dna1)	{
	dna1<-toupper(dna1)
	dna1<-gsub("[^AGTC]",'',dna1)
	return(dna1)
}

returnMatch<-function (base)	{
	if (base=="G")	{
		return("C")
	}
	if (base=="C")	{
		return("G")
	}
	if (base=="A")	{
		return("T")
	}
	if (base=="T")	{
		return("A")
	}	
}

complementaryDNA<-function (dna1)	{
#dna1_a<-cleanDNA(unlist(strsplit(dna1,split="")))
	dna1_a<-unlist(strsplit(cleanDNA(dna1),split=""))
	complementary_strand<-""
	for (i in 1:length(dna1_a))	{
		complementary_strand_n<-paste(complementary_strand,returnMatch(dna1_a[i]),sep="")		
		complementary_strand<-complementary_strand_n
	}
	return (complementary_strand)
}

demarcate_mutant_bases<-function (wild,mutant)	{
	if (nchar(wild) != nchar(mutant))	{
		return (mutant)
	}
	wild<-unlist(strsplit(wild,''))
	mutant<-unlist(strsplit(mutant,''))
	tf_matrix<-as.character(wild)==as.character(mutant)
	mutant_string<-''
	for (i in 1:length(tf_matrix))	{
		if (tf_matrix[i] == "FALSE")	{
			mutant_string<-paste(mutant_string,"(",mutant[i],")",sep="")
		}
		if (tf_matrix[i] == "TRUE")	{
			mutant_string<-paste(mutant_string,mutant[i],sep="")
		}
	}
	return (mutant_string)
}

#getPeakIndex
#function to decide the index of the dispensaton sequence to which the peak should be added
#needed 0) nucleotide to be added to sequence 1)dispensation sequence, 2)last index to receive the peak, 
#{{{{ACTUALLY THIS CAN BE FIGURED OUT FROM (2) 3)last base pair to be sent to the function}}}}
# if current base pair = complementaryDNA(dispensation_sequence[last index to receive a peak]) 
#----> Add a peak to the last index to receive peak (ie return the last index)
#otherwise, find the minimum index that == nucleotide to be added && > last index to receive a peak

getPeakIndex<- function (nucleotide,last_pk,dispensation)	{
	if (nucleotide == returnMatch(dispensation[last_pk]))	{
		return (last_pk)
	}
	nucmatch<-which(dispensation == returnMatch(nucleotide))
	return (nucmatch[min(which(nucmatch > last_pk))])
#the returnMatch use is there because the dispensation is complementary to the sequence being sequenced...
}

#getFactor
#the nucleotide "A" gets 1.1* intensity, but given that this is variable, the factor will be determined
#by a function
getFactor<-function(base)	{
	if (base=="G")	{
		return(1)
	}
	if (base=="C")	{
		return(1)
	}
	if (base=="A")	{
		return(1.1)
	}
	if (base=="T")	{
		return(1)
	}
}

#addSignal
#function to add a curve to the y vector
#input 
#(1) index of the nucleotide on which the peak will be based (this is also the integer in the x vector)
#(2) y vector to which the signal is added
#(3) standard deviation
#(4) correction factor (ie - A is 1.1* C|G|T)

addSignal<-function (x,y,centroid,s,factor,peak_shape)	{
	if (peak_shape == 'full gaussian')	{
		return(add_full_gaussian(x,y,centroid,s,factor))
	}
	if (peak_shape == 'half gaussian')	{
		return(add_half_gaussian(x,y,centroid,s,factor))
	}
	if (peak_shape == 'spike')	{
		return(add_spike(x,y,centroid,factor))
	}
}

#############add_spike

add_spike <- function (x,y,centroid,factor)	{
	y_index_centroid<-which.min(abs(x-centroid))
	y[y_index_centroid]<-y[y_index_centroid]+factor
	return (y)
}

add_half_gaussian <- function (x,y,centroid,s,factor)	{
#x and y must be of same length - need to get the function to die if they are not
	y_indices<-which(x>centroid-4*s & x<centroid+4*s)
	
	y_index_centroid<-which.min(abs(x-centroid))
	
	y_indices_plus<-which(x>=centroid & x<centroid+4*s)
	y_indices_minus<-which(x<centroid & x>centroid-.5*s)
	
	y[y_indices_plus]<-y[y_indices_plus] + factor*(1/(s*sqrt(2*pi))*exp(-((x[y_indices_plus]-centroid)^2)/(2*s^2)))
	
#slope <- (y[which.max(y[y_indices_plus])] - y[which.min(y[y_indices_minus])])/(x[which.max(x[y_indices_plus])] - x[which.min(x[y_indices_minus])])
	slope<-((y[y_indices_plus[which.max(y[y_indices_plus])]])/((x[y_index_centroid])-(x[min(y_indices_minus)])))
	y[y_indices_minus]<-slope*((x[y_indices_minus])-(x[y_indices_minus[which.min(x[y_indices_minus])]]))
	return (y)
	
}
get_half_gaussian_max <- function (x,y,centroid,s,factor)  {
  #x and y must be of same length - need to get the function to die if they are not
  y_indices<-which(x>centroid-4*s & x<centroid+4*s)
  
  y_index_centroid<-which.min(abs(x-centroid))
  
  y_indices_plus<-which(x>=centroid & x<centroid+4*s)
  y_indices_minus<-which(x<centroid & x>centroid-.5*s)
  
  y[y_indices_plus]<-factor*(1/(s*sqrt(2*pi))*exp(-((x[y_indices_plus]-centroid)^2)/(2*s^2)))
  
  #slope <- (y[which.max(y[y_indices_plus])] - y[which.min(y[y_indices_minus])])/(x[which.max(x[y_indices_plus])] - x[which.min(x[y_indices_minus])])
  slope<-((y[y_indices_plus[which.max(y[y_indices_plus])]])/((x[y_index_centroid])-(x[min(y_indices_minus)])))
  y[y_indices_minus]<-slope*((x[y_indices_minus])-(x[y_indices_minus[which.min(x[y_indices_minus])]]))
  return ( y[y_indices_plus[which.max(y[y_indices_plus])]])
  
}
add_full_gaussian<-function (x,y,centroid,s,factor)	{
	y_indices<-which(x>centroid-4*s & x<centroid+4*s)
	y[y_indices]<-y[y_indices] + factor*(1/(s*sqrt(2*pi))*exp(-((x[y_indices]-centroid)^2)/(2*s^2)))
	return (y)
}

#getHeightOneBase
#For graphical purposes it is important to know the signal produced by one base
#use the same function in addSignal to get the peak maximum off of one centroid that has the same standard sd
#and is centered on 1.

getHeightOneBase<-function(x,y,centroid,s,factor,peak_shape)	{
	y<-addSignal(x,y,centroid,s,factor,peak_shape)
	return (max(y))
}


#######################################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################
#Parameter declaraion##################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################
#######################################################################################################

args<-commandArgs(TRUE)
if (length(args) > 0)	{
	parameters<-read.table(as.character(args[1]),header=T,sep="\t")
	rownames(parameters)<-parameters$names
	seqs<-unlist(strsplit(as.character(parameters["sequences",2]),","))
	seq_names<-unlist(strsplit(as.character(parameters["sequence names",2]),","))
	tumor_percent<-as.numeric(as.character(parameters["tumor percent",2]))/100
	seq_ratios<-c(1-tumor_percent)	
	mutation_percent<-as.numeric(unlist(strsplit(as.character(parameters["mutation percent",2]),",")))/100
	chromosome_status<-unlist(strsplit(as.character(parameters["chromosome status",2]),","))
	trace_type<-as.character(parameters["trace type",2])
	add_annotation<-as.character(parameters["add annotation",2])
	dispensation_type<-as.character(parameters["dispensation type",2])
	seq_colors<-unlist(strsplit(as.character(parameters["sequence colors",2]),","))
	if (length(seqs) > 1)	{
		for (i in 2:length(seqs))	{
			if(chromosome_status[i-1] == 'HETEROZYGOUS')	{
				seq_ratios<-c(seq_ratios,0.5*tumor_percent*mutation_percent[i-1])
				seq_ratios[1]<-seq_ratios[1]+0.5*tumor_percent*mutation_percent[i-1]
			}	
			if(chromosome_status[i-1] == 'HOMOZYGOUS')	{
				seq_ratios<-c(seq_ratios,tumor_percent*mutation_percent[i-1])
			}
			if(chromosome_status[i-1] == 'HEMIZYGOUS')	{
				seq_ratios<-c(seq_ratios,0.5*tumor_percent*mutation_percent[i-1])
			}
		}
	}

	trace_type<-as.character(parameters["trace type",2])
	if (trace_type == "SANGER")		{
		peak_shape<-"full gaussian"
		separate_traces<-"FALSE"
		s<-as.numeric(as.character(parameters["peak width",2]))
		if (is.na(s))	{
			s<-0.2
		}
		correction_factor<-as.numeric(as.character(parameters["correction factor",2]))
		if (is.na(correction_factor))	{
			correction_factor<-1
		}
		pts_per_base<-as.numeric(as.character(parameters["points per base",2]))
		if (is.na(pts_per_base))	{
			pts_per_base<-50
		}
		yaxis_increment<-as.numeric(as.character(parameters["yaxis increment",2]))
		if (is.na(yaxis_increment))	{
			yaxis_increment<-0.5
		}

		bases_per_plot<-as.numeric(as.character(parameters["bases per line",2]))
		if (is.na(bases_per_plot))	{
			bases_per_plot<-max(nchar(seqs))+1
		}
		plots_per_page<-as.numeric(as.character(parameters["lines per page",2]))
		if (is.na(plots_per_page))	{
			plots_per_page<-3
		}
		LOD<-as.numeric(as.character(parameters["limit of detection",2]))/100
		if (is.na(LOD))	{
			LOD<-0.2
		}
		
		save_to_file<-as.character(parameters["save to file",2])
		dir<-as.character(parameters["directory",2])
		fp_in<-as.character(parameters["file path",2])
		
	}
	if (trace_type == "PYRO")		{
		peak_shape<-"spike"
		separate_traces<-as.character(parameters["separate traces",2])
		
		s<-as.numeric(as.character(parameters["peak width",2]))
		if (is.na(s))	{
			s<-0.1
		}
		
		correction_factor<-as.numeric(as.character(parameters["correction factor",2]))
		if (is.na(correction_factor))	{
			correction_factor<-50
		}

		pts_per_base<-as.numeric(as.character(parameters["points per base",2]))
		if (is.na(pts_per_base))	{
			pts_per_base<-50
		}

		yaxis_increment<-as.numeric(as.character(parameters["yaxis increment",2]))
		if (is.na(yaxis_increment))	{
			yaxis_increment<-25
		}
		
		yaxis_increment
		
		LOD<-as.numeric(as.character(parameters["limit of detection",2]))/100
		if (is.na(LOD))	{
			LOD<-0.01
		}
		
#For pyrosequencing, the limit of detection is so low that the addition of low intensity signal to high intensity signal is of negligible importance, so it is ignored
		seqs<-seqs[which(seq_ratios > LOD)]
		seq_names<-seq_names[which(seq_ratios > LOD)]
		mutation_percent<-mutation_percent[which(seq_ratios[2:length(seq_ratios)] > LOD)]
		chromosome_status<-chromosome_status[which(seq_ratios[2:length(seq_ratios)] > LOD)]
		seq_ratios<-seq_ratios[which(seq_ratios > LOD)]
		if (separate_traces == "TRUE")	{
			seq_colors<-seq_colors[which(seq_ratios > LOD)]
		}
		
		
		save_to_file<-as.character(parameters["save to file",2])
		dir<-as.character(parameters["directory",2])
# directory<-as.character(parameters["directory",2])
		dispensation<-complementaryDNA(parameters["dispensation",2])
		fp_in<-as.character(parameters["file path",2])
		
	}
	
}

#######################################################################################################
#######################################################################################################
#END of Parameter declaraion###########################################################################
#######################################################################################################
#######################################################################################################


#######################################################################################################
#######################################################################################################
#BEGINNING of SANGER SEQUENCING########################################################################
#######################################################################################################
#######################################################################################################

if (trace_type == "SANGER")	{
	max_last_pk<-0
	n_plots<-1+as.integer(max(nchar(seqs))/bases_per_plot)
	final_n_bases<-n_plots*bases_per_plot
	
	#create the file_path if the trace is the be saved to a file.
	if (save_to_file == "TRUE")	{
		if (separate_traces == "FALSE")	{
			file_path<-paste(dir,paste(paste(seq_names,collapse="-"),"SANGER",sep="_"),sep="")
		}
#file_path<-dir
	}
#anytime the file path is stated, that trumps the automatic name
	if (length (fp_in) >0)	{
		file_path<-fp_in
	}
	
	
	pre_x<-seq(from=0,to=1+final_n_bases,length=1+final_n_bases)
	pre_y_A<-array(data=0,dim=1+final_n_bases)
	pre_y_G<-array(data=0,dim=1+final_n_bases)
	pre_y_T<-array(data=0,dim=1+final_n_bases)
	pre_y_C<-array(data=0,dim=1+final_n_bases)
	
	for (seq in 1:length(seqs))	{
		seq1<-seqs[seq]
		seq1_a<-unlist(strsplit(as.character(seq1),split=""))
		
		seq1_a_G<-seq1_a
		seq1_a_G[which(seq1_a_G != "G")]<-0
		seq1_a_G[which(seq1_a_G == "G")]<-1
		seq1_a_G<-as.numeric(seq1_a_G)
		pre_y_G<-pre_y_G[1:length(seq1_a_G)]+seq1_a_G*seq_ratios[seq]
		
		seq1_a_C<-seq1_a
		seq1_a_C[which(seq1_a_C != "C")]<-0
		seq1_a_C[which(seq1_a_C == "C")]<-1
		seq1_a_C<-as.numeric(seq1_a_C)
		pre_y_C<-pre_y_C[1:length(seq1_a_C)]+seq1_a_C*seq_ratios[seq]
		
		seq1_a_A<-seq1_a
		seq1_a_A[which(seq1_a_A != "A")]<-0
		seq1_a_A[which(seq1_a_A == "A")]<-1
		seq1_a_A<-as.numeric(seq1_a_A)
		pre_y_A<-pre_y_A[1:length(seq1_a_A)]+seq1_a_A*seq_ratios[seq]
		
		seq1_a_T<-seq1_a
		seq1_a_T[which(seq1_a_T != "T")]<-0
		seq1_a_T[which(seq1_a_T == "T")]<-1
		seq1_a_T<-as.numeric(seq1_a_T)
		pre_y_T<-pre_y_T[1:length(seq1_a_T)]+seq1_a_T*seq_ratios[seq]
		
	}
	
	x<-seq(from=0,to=1+final_n_bases,length=pts_per_base*(1+final_n_bases))
	y_A<-array(data=0,dim=pts_per_base*max(1+final_n_bases))
	y_G<-array(data=0,dim=pts_per_base*max(1+final_n_bases))
	y_T<-array(data=0,dim=pts_per_base*max(1+final_n_bases))
	y_C<-array(data=0,dim=pts_per_base*max(1+final_n_bases))
	
	pre_y_A[which(pre_y_A < LOD)]<-0
	pre_y_G[which(pre_y_G < LOD)]<-0
	pre_y_T[which(pre_y_T < LOD)]<-0
	pre_y_C[which(pre_y_C < LOD)]<-0
	
		
	for (i in 1:length(pre_y_A))	{
		y_A<-addSignal(x,y_A,i,s,pre_y_A[i],peak_shape)	
	}
	for (i in 1:length(pre_y_G))	{
		y_G<-addSignal(x,y_G,i,s,pre_y_G[i],peak_shape)	
	}
	for (i in 1:length(pre_y_T))	{
		y_T<-addSignal(x,y_T,i,s,pre_y_T[i],peak_shape)	
	}
	for (i in 1:length(pre_y_C))	{
		y_C<-addSignal(x,y_C,i,s,pre_y_C[i],peak_shape)	
	}
	
	
	final_xy_length<-pts_per_base*(max(nchar(seqs))+0.5)
	
	
	#seq_ratios<-seq_ratios/(max(seq_ratios)*one_base_height)
	dna_seq_ratios<-seq_ratios*100
	#seq_ratios<-seq_ratios/(one_base_height)
	
	#y_A<-y_A*one_base_height/max(c(y_A,y_G,y_T,y_C))
	#y_G<-y_G*one_base_height/max(c(y_A,y_G,y_T,y_C))
	#y_T<-y_T*one_base_height/max(c(y_A,y_G,y_T,y_C))
	#y_C<-y_C*one_base_height/max(c(y_A,y_G,y_T,y_C))
	
	
	
	#for (i in 1:length(seqs))	{
	#	magnitude_list[[i]]<-magnitude_list[[i]]/one_base_height
	#}
	
	if (max(nchar(seqs)) <= 25)	{
#legend_names<-paste(paste(paste(paste(seq_names,seqs,sep="-"),signif(dna_seq_ratios,digits=3),sep=" "),"%",sep=""),"total DNA",sep=" ")
		legend_names<-paste(paste(paste(paste(seq_names[which(dna_seq_ratios)>LOD],seqs[which(dna_seq_ratios)>LOD],sep="-"),signif(dna_seq_ratios[which(dna_seq_ratios)>LOD],digits=3),sep=" "),"%",sep=""),"total DNA",sep=" ")
		if (length(seq_names) > 1)	{
			legend_names[2:length(legend_names)]<-paste(legend_names[2:length(legend_names)]," (tumor cellularity = ",tumor_percent*100,"%, ","mutation = ",mutation_percent*100,"%, ",chromosome_status,")",sep="")
	
		}
	}
	
	if (max(nchar(seqs)) > 25)	{
		legend_names<-paste(paste(paste(seq_names,signif(dna_seq_ratios,digits=3),sep=" "),"%",sep=""),"total DNA",sep=" ")
		if (length(seq_names) > 1)	{
			legend_names[2:length(legend_names)]<-paste(legend_names[2:length(legend_names)]," (tumor cellularity = ",tumor_percent*100,"%, mutation: ",chromosome_status,")",sep="")
	
		}
	}
	
	if (save_to_file == "TRUE")	{
#pdf(file_path,width=11,height=8.5)
		png(file_path,width=800,height=600,units="px")
	}
	
	if (n_plots>plots_per_page)	{
		n_plots<-plots_per_page
	}
	
	par(mfcol=c(n_plots,1))
	
	if (separate_traces == "FALSE")	{
		
		ymax<-yaxis_increment*max(2+as.integer((max(y_A,y_G,y_C,y_T))*correction_factor/yaxis_increment))
		
	#A=green
	#T=red
	#C=blue
	#G=black
		for (i in 1:n_plots)	{
			
			minval<-1+(i-1)*bases_per_plot*pts_per_base
			maxval<-i*(bases_per_plot+0.5)*pts_per_base
			minpos<-(i-1)*bases_per_plot
			maxpos<-0.5+i*bases_per_plot
			
			plot.default(x[minval:maxval],y_A[minval:maxval]*correction_factor,xlim=c(minpos,maxpos),ylim=c(0,ymax),xaxt="n",xaxs="i",type="l",ylab="Intensity",xlab="Position",col="green")
			points(.01+x[minval:maxval],y_T[minval:maxval]*correction_factor,xlim=c(minpos,maxpos),ylim=c(0,ymax),xaxt="n",xaxs="i",type="l",ylab="Intensity",xlab="Position",col="red")
			points(.02+x[minval:maxval],y_C[minval:maxval]*correction_factor,xlim=c(minpos,maxpos),ylim=c(0,ymax),xaxt="n",xaxs="i",type="l",ylab="Intensity",xlab="Position",col="blue")
			points(.03+x[minval:maxval],y_G[minval:maxval]*correction_factor,xlim=c(minpos,maxpos),ylim=c(0,ymax),xaxt="n",xaxs="i",type="l",ylab="Intensity",xlab="Position",col="black")
			legend("topleft",paste(legend_names,collapse="\n"),lty=1,lwd=2,bty="n",col="white")
			axis(1,at=seq(from=0,to=as.numeric(final_n_bases)))
		}
	}
	
	
	
	if (save_to_file == "TRUE")	{
		dev.off()
	}
}

#######################################################################################################
#######################################################################################################
#END of SANGER SEQUENCING##############################################################################
#######################################################################################################
#######################################################################################################


#######################################################################################################
#######################################################################################################
#BEGIN PYROSEQUENCING##################################################################################
#######################################################################################################
#######################################################################################################

if (trace_type=="PYRO")	{
	
	max_last_pk<-0
	
	
	
#create the file_path if the trace is the be saved to a file.
	if (save_to_file == "TRUE")	{
		if (separate_traces == "TRUE")	{
			file_path<-paste(dir,paste(paste(seq_names,collapse="-"),complementaryDNA(dispensation),"SEPARATE",sep="_"),sep="")
		}
		if (separate_traces == "FALSE")	{
			file_path<-paste(dir,paste(paste(seq_names,collapse="-"),"SINGLE",sep="_"),sep="")
		}
	}
#anytime the file path is stated, that trumps the automatic name
	if (length (fp_in) >0)	{
		file_path<-fp_in
	}
	
	file_path1<-file_path
#file_path<-paste(file_path,".pdf",sep="")
#file_path2<-paste(file_path1,"vectors.csv",sep="")
#Dispensation is complementary
#the dispensation array is constant across all sequences
#the maximum length of the dispensation array = 
#	the number of bases of the longest sequence * length of dispensation sequence
	
	disp_temp<-paste(array(data=dispensation,dim=nchar(dispensation)*max(nchar(seqs))),collapse="")
	disp_temp_a<-unlist(strsplit(as.character(disp_temp),split=""))
	
	magnitude_list<-list()
	
#	x<-seq(from=1,length(disp_temp_a),length=pts_per_base*length(disp_temp_a))
#	y<-array(data=0,dim=pts_per_base*length(disp_temp_a))
	
	#x<-seq(from=0.5,length(disp_temp_a),length=pts_per_base*(length(disp_temp_a)+0.5))
	#y<-array(data=0,dim=pts_per_base*(length(disp_temp_a)+0.5))

	x<-seq(from=0.5,to=length(disp_temp_a)+.5,length=length(disp_temp_a)+1)
	y<-array(data=0,dim=length(disp_temp_a)+1)
	
	
	one_base_height<-getHeightOneBase(x[1:(2*pts_per_base)],array(data=0,dim=2*pts_per_base),1.5,s,1,peak_shape)
	
  #I want to make annotation easy
  #the only way to make annotation easy is to make a single dispensation nucleotide vector for each sequence
  #and subtract the final answer from the wildtype
  #
  #so, if I want to do a spike vector first (to make annotation straightforward)
  #then I need to run through the sequence
  
	for (seq in 1:length(seqs))	{
		
#the x vector is constant since it is tied to the dispensation sequence
#the y vector is the sequence specific vector of graph magnitudes - must be reset for each sequence
#reset the y 
	  y<-array(data=0,dim=length(disp_temp_a)+1)
		seq1<-seqs[seq]
		seq1_a<-unlist(strsplit(as.character(seq1),split=""))
		
#If the first position on the dispensation sequence needed to be determined separately, 
#this is how it would be done - 
#	y<-addSignal(x,y,getPeakIndex(seq1_a[1],1,disp_temp_a),s,getFactor(seq1_a[1]))
#	last_pk<-getPeakIndex(seq1_a[1],1,disp_temp_a)
#REMEMBER - if you do this this way, the forloop needs to be for i in 2!!!!!:length(seq1_a)
		
		last_pk<-1
		for (i in 1:length(seq1_a))	{
			y<-addSignal(x,y,getPeakIndex(seq1_a[i],last_pk,disp_temp_a),s,getFactor(seq1_a[i]),"spike")
			last_pk<-getPeakIndex(seq1_a[i],last_pk,disp_temp_a)
		}
		
		if (last_pk > max_last_pk)	{ 
			max_last_pk<-last_pk
		}
		
		magnitude_list[[seq]]<-y
	}
		
#If the dispensation is longer than the number of peaks required to map out the sequence, then only the dispensation needs to be plotted (first if)
#But if the dispensation shorter than what is required for the sequence, then it gets repeated, 
#and the final n of bases to plot is the final peak + one more repeat of the dispensation
	if (max_last_pk <= nchar(dispensation))	{
		final_n_bases<-nchar(dispensation)
	}
	
	if (max_last_pk > nchar(dispensation))	{
		final_n_bases<-max_last_pk
	}
#Of course, after all of that, if the dispensation type is fixed, then that trumps everything
  
  if (dispensation_type == "FIXED"){
    final_n_bases<-nchar(dispensation)
  }
	
	#final_xy_length<-pts_per_base*(max_last_pk+nchar(dispensation)+0.5)
	#final_xy_length<-pts_per_base*(max_last_pk+0.5)

  final_xy_length<-pts_per_base*(final_n_bases+1)
  final_xy_length_spike<-final_n_bases
	for (seq in 1:length(seqs))	{
	  y<-magnitude_list[[seq]]
	  y<-y[1:final_xy_length_spike]
	  #write.table(y,file=paste(file_path1,seq,"vectors.csv",sep=""))
    if (seq==1){
      wild_y<-y
    }
	}	
	
#seq_ratios<-seq_ratios/(max(seq_ratios)*one_base_height)
	dna_seq_ratios<-seq_ratios*100
	seq_ratios<-seq_ratios/(one_base_height)
	
	
#for (i in 1:length(seqs))	{
#	magnitude_list[[i]]<-magnitude_list[[i]]/one_base_height
#}
	
	if (max(nchar(seqs)) <= 25)	{
#legend_names<-paste(paste(paste(paste(seq_names,seqs,sep="-"),signif(dna_seq_ratios,digits=3),sep=" "),"%",sep=""),"total DNA",sep=" ")
#legend_names[2:length(legend_names)]<-paste(legend_names[2:length(legend_names)]," (tumor cellularity = ",tumor_percent*100,"%, ","mutation = ",mutation_percent*100,"%, ",chromosome_status,")",sep="")
		diff_highlighted<-c(seqs[1])

		if (length(seq_names) > 1)	{

			for (diff_string in 2:length(seqs))	{
				diff_highlighted<-c(diff_highlighted,demarcate_mutant_bases(seqs[1],seqs[diff_string]))
			}
		}
#legend_names<-paste(paste(paste(paste(seq_names[which(dna_seq_ratios>LOD*100)],seqs[which(dna_seq_ratios>LOD*100)],sep="-"),signif(dna_seq_ratios[which(dna_seq_ratios>LOD*100)],digits=3),sep=" "),"%",sep=""),"total DNA",sep=" ")
		legend_names<-paste(
      paste(
        paste(
          paste(
            seq_names[which(dna_seq_ratios>LOD*100)],
            diff_highlighted[which(dna_seq_ratios>LOD*100)],
            sep="-"),
          signif(dna_seq_ratios[which(dna_seq_ratios>LOD*100)],digits=3),
          sep=" "),
        "%",
        sep=""),
      "total DNA",sep="")

		if (length (legend_names) > 1)	{
			legend_names[2:length(legend_names)]<-paste(legend_names[2:length(legend_names)]," (tumor cellularity = ",tumor_percent*100,"%,","\nmutation = ",mutation_percent*100,"%, ",chromosome_status,")",sep="")
			
		}
		
	}
	
	if (max(nchar(seqs)) > 25)	{
		legend_names<-paste(paste(paste(seq_names,signif(dna_seq_ratios,digits=3),sep=" "),"%",sep=""),"total DNA",sep=" ")
		if (length(seq_names) > 1)	{
			legend_names[2:length(legend_names)]<-paste(legend_names[2:length(legend_names)]," (tumor cellularity = ",tumor_percent*100,"%, mutation: ",chromosome_status,")",sep="")
		}
	}
	
	if (save_to_file == "TRUE")	{
		#file_path<-paste(file_path,".pdf",sep="")
		#pdf(file_path,width=11,height=8.5)
		#Alan file_path<-paste(file_path,".png",sep="")
		png(file_path,width=800,height=600,units="px")
	}
	
	if (separate_traces == "TRUE")	{
		attenuated_magnitudes<-list()
		
		for (i in 1:length(seqs))	{
			if (i <= 1)	{
				master_y<-seq_ratios[i]*magnitude_list[[i]][1:final_xy_length]
				attenuated_magnitudes[[i]]<-magnitude_list[[i]][1:final_xy_length]*seq_ratios[i]
				large_vector<-magnitude_list[[i]][1:final_xy_length]*seq_ratios[i]
			}
			if (i>1)	{
				master_y<-master_y + seq_ratios[i]*magnitude_list[[i]][1:final_xy_length]
				attenuated_magnitudes[[i]]<-magnitude_list[[i]][1:final_xy_length]*seq_ratios[i]
				large_vector<-magnitude_list[[i]][1:final_xy_length]*seq_ratios[i]
			}
		}
		
		ymax<-yaxis_increment*max(2+as.integer(master_y*correction_factor/yaxis_increment))
		
		
		for (i in 1:length(seqs))	{
			if (i <= 1)	{
				plot.default(x[1:final_xy_length],
                     attenuated_magnitudes[[i]][1:final_xy_length]*correction_factor,
                     ylim=c(0,ymax),
                     xlim=c(0,final_n_bases + 0.5),
                     xaxt="n",
                     xaxs="i",
                     type="l",
                     ylab="Intensity",
                     xlab="Dispensation",
                     col=seq_colors[1])
			}
      
      
      
			if (i>1)	{
				lines(x[1:final_xy_length],
              attenuated_magnitudes[[i]][1:final_xy_length]*correction_factor,
              ylim=c(0,ymax),
              xlim=c(0,final_n_bases + 0.5),
              xaxt="n",
              type="l",
              new=FALSE,
              col=seq_colors[i])	
			}
		}
		
		#legend("topleft",legend_names,col=seq_colors,lty=1,lwd=2,bty="n")
	#	mtext(paste(legend_names,sep="\n"),side=3,col=seq_colors,lty=1,lwd=2,bty="n")
		abline(h=c(seq(from=yaxis_increment,to=ymax,by=yaxis_increment)),lty="dotted")
		axis(2,at=seq(from=yaxis_increment,to=ymax,by=yaxis_increment))
		
	}
	
	if (separate_traces == "FALSE")	{
    #Now generate the master trace (note we are still at spike level here)
    for (i in 1:length(seqs))	{
			if (i <= 1)	{
				master_y<-seq_ratios[i]*magnitude_list[[i]][1:final_xy_length_spike]
			}
			if (i>1)	{
				master_y<-master_y + seq_ratios[i]*magnitude_list[[i]][1:final_xy_length_spike]
			}
		}
		#write.table(master_y,file=paste(file_path1,seq,"mastervector.csv",sep=""),sep="\t")	
    #and get difference vector between the master vector and the wild sequence
    
    #difference_vector<-master_y-wild_y
    difference_vector<-wild_y-master_y
    
    printable_difference_vector<-which(difference_vector != 0)
    magprintable_difference_vector<-difference_vector[which(difference_vector != 0)]
        #sink(paste(file_path1,seq,"difference_vector.csv",sep=""))
        #sink()
		#cat(paste(magprintable_difference_vector,collapse="-"),file=paste(file_path1,seq,"difference_vector.csv",sep=""))
        #cat(paste(seq_names,collapse="-"),file=paste(directory,"difference_vector.csv",sep=""),append=TRUE)
        #cat("\t",file=paste(directory,"difference_vector.csv",sep=""),append=TRUE)
        #cat(paste(printable_difference_vector,collapse="*"),file=paste(directory,"difference_vector.csv",sep=""),append=TRUE)
        #cat("\n",file=paste(directory,"difference_vector.csv",sep=""),append=TRUE)
        #sink()
        
        #  write.table(cat(printable_difference_vector,sep="*"),file=paste(file_path1,seq,"difference_vector.csv",sep=""),sep="\t")
    
    #need to get pyrgram-heights for 1X,2X,3X,4X,5X
    barheights<-1:5
    bars<-barheights
    barx<-seq(from=0.5,to=max(barheights+.05),length=pts_per_base*length(barheights))
    bary<-array(data=0,dim=length(barx))
    for (bar in 1:length(barheights)){
      bars[bar]<-get_half_gaussian_max(barx,bary,bar,s,barheights[bar])
    }    

    #preparing to map the spikes to pyrogram peak shapes
    plotx<-seq(from=0.5,final_n_bases+0.5,length=final_xy_length)
    ploty2<-array(data=0,dim=final_xy_length)
    maxima<-array(data=0,dim=length(master_y))
    pos<-array(data=0,dim=length(master_y))
    
    for (position in 1:length(master_y)){
      ploty2<-add_half_gaussian(plotx,ploty2,position,s,master_y[position])
      maxima[position]<-get_half_gaussian_max(plotx,ploty2,position,s,master_y[position])
		  pos[position]<-position
      #for (barht in 1:5){
       # dbht<-abs(master_y[position]-barht)
        #if (dbht<.01){
        #  barheights[barht]<-maxima[position]
        #}
      #}
    }
    #write.table(barheights,file=paste(file_path1,seq,"barheights.csv",sep=""),sep="\t")
    ploty<-ploty2/4
    maxima<-maxima/4
    barheights<-bars
    barheights<-(barheights+.05)/4
    #write.table(maxima,file=paste(file_path1,seq,"maxima_vector.csv",sep=""),sep="\t")

    #ploty<-ploty2
    ymax<-yaxis_increment*max(1+as.integer(ploty*correction_factor/yaxis_increment))
    #ymax<-175
    barheights<-barheights[which(barheights>.05/4 & barheights*correction_factor<ymax)]
    
    
#plot.default(x[1:final_xy_length],master_y*correction_factor,xlim=c(0,final_n_bases + 0.5),ylim=c(0,ymax),xaxt="n",xaxs="i",type="l",ylab="Intensity",xlab="Dispensation",lwd=2)
	#	plot.default(x[1:final_xy_length],master_y*correction_factor,xlim=c(0,final_n_bases + 0.5),ylim=c(0,ymax),axes=FALSE, xaxt="n",xaxs="i",type="l",ylab="Intensity",xlab="Dispensation",lwd=2)
		par(oma=c(1.5,2,2,0))
    plot.default(plotx,
                 ploty*correction_factor,
                 xlim=c(0,final_n_bases + 0.5),
                 ylim=c(0,ymax),
                 xaxs="i",
                 axes=FALSE, 
                 xlab="",
                 ylab="",
                 type="l",
                 lwd=2        
                 )
   
    box(lty=1,col="black")
    #abline(h=c(seq(from=yaxis_increment,to=ymax,by=yaxis_increment)),lty="dotted")
    
    difference_vector[which(abs(difference_vector)<.0001)]<-0
    
    ###lwd should be 4

    if (add_annotation == "TRUE"){
      arrows(pos[which(difference_vector>0)]+.25,
             wild_y[which(difference_vector>0)]*correction_factor,
             pos[which(difference_vector>0)]+.25,
             maxima[which(difference_vector>0)]*correction_factor,           
             angle=20,
             col="black",
             lwd=8)
    
      arrows(pos[which(difference_vector<0)]-.25,
             wild_y[which(difference_vector<0)]*correction_factor,
             pos[which(difference_vector<0)]-.25,
             maxima[which(difference_vector<0)]*correction_factor,           
             col="black",
             angle=20,
             lwd=8)
     
      #legend("topleft",paste(legend_names,collapse="\n"),col="black",lty=1,lwd=2,bty="n")
   }
      abline(h=barheights*correction_factor,lwd=3)
      text(1,y=barheights*correction_factor,labels=paste(seq(from=1,to=length(barheights),by=1),"X",sep=""),cex=2.5,pos=3)

 #####This is just for the paper   mtext(paste(legend_names,sep=" ",collapse="\n"),side=3,col=seq_colors,lty=1,lwd=2,bty="n")

    mtext("Dispensation",
          side=1,
          cex=2,
          outer=TRUE)
    mtext("Intensity",
          side=2,
          cex=2,
          outer=TRUE)
    mtext (paste(difference_vector,collapse=","),
            side=3,
            cex=1,
            outer=TRUE)

    axis(2,at=seq(from=0,to=ymax,by=yaxis_increment),las=1,cex.axis=1.5)
	}
	
	c_disp_temp_a<-disp_temp_a
	for (i in 1:length(c_disp_temp_a))	{
		c_disp_temp_a[i]<-complementaryDNA(disp_temp_a[i])
	}

  if (final_n_bases>=40){
    cexf<-0.5
    axis(1,at=1:final_n_bases,c_disp_temp_a[1:final_n_bases],tck=0.025,par(cex.axis=cexf))
  }
    
  if(final_n_bases>20 & final_n_bases<40){
	 cexf<-1.5 
	 axis(1,at=1:final_n_bases,c_disp_temp_a[1:final_n_bases],tck=0.025,par(cex.axis=cexf))
	 axis(1,at=2:final_n_bases,c_disp_temp_a[2:final_n_bases],tck=0.025,par(cex.axis=cexf))
  }

  if (final_n_bases<=20){
	 cexf<-1.5
	 axis(1,at=1:final_n_bases,c_disp_temp_a[1:final_n_bases],tck=0.025,par(cex.axis=cexf))
  }
	
	
	if (save_to_file == "TRUE")	{
		dev.off()
	}
	
	
}

#######################################################################################################
#######################################################################################################
#END PYROSEQUENCING##################################################################################
#######################################################################################################
#######################################################################################################
